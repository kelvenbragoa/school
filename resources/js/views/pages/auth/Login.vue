<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const toast = useToast();
const { login } = useAuth();

const email = ref('');
const password = ref('');
const checked = ref(false);
const loading = ref(false);

const handleLogin = async () => {
    if (!email.value || !password.value) {
        toast.add({
            severity: 'warn',
            summary: 'Atenção',
            detail: 'Por favor, preencha email e senha',
            life: 3000
        });
        return;
    }

    loading.value = true;

    try {
        const result = await login(email.value, password.value);

        if (result.success) {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: `Bem-vindo, ${result.user.name}!`,
                life: 3000
            });

            // Redirecionar para o dashboard
            setTimeout(() => {
                router.push('/admin');
            }, 500);
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: result.message || 'Credenciais inválidas',
                life: 3000
            });
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Erro ao fazer login. Tente novamente.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <FloatingConfigurator />
    <Toast />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 1px; background-color: aliceblue;">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Stock Management</div>
                        <span class="text-muted-color font-medium">Faça login para continuar</span>
                    </div>

                    <form @submit.prevent="handleLogin">
                        <label for="email1" class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">Email</label>
                        <InputText 
                            id="email1" 
                            type="email" 
                            placeholder="Digite seu email" 
                            class="w-full md:w-[30rem] mb-8" 
                            v-model="email"
                            :disabled="loading"
                        />

                        <label for="password1" class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2">Password</label>
                        <Password 
                            id="password1" 
                            v-model="password" 
                            placeholder="Digite sua senha" 
                            :toggleMask="true" 
                            class="mb-4" 
                            fluid 
                            :feedback="false"
                            :disabled="loading"
                        ></Password>

                        <div class="flex items-center justify-between mt-2 mb-8 gap-8">
                            <div class="flex items-center">
                                <Checkbox v-model="checked" id="rememberme1" binary class="mr-2" :disabled="loading"></Checkbox>
                                <label for="rememberme1">Lembrar-me</label>
                            </div>
                        </div>
                        <Button 
                            type="submit" 
                            label="Entrar" 
                            class="w-full" 
                            :loading="loading"
                        ></Button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
