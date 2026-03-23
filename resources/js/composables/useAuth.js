import { ref, computed } from 'vue';
import axios from 'axios';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

const token = ref(localStorage.getItem(TOKEN_KEY) || null);
const user = ref(JSON.parse(localStorage.getItem(USER_KEY) || 'null'));

// Flag para controlar se os interceptors já foram configurados
let interceptorsConfigured = false;

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);
    
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isGestor = computed(() => user.value?.role === 'gestor');
    const isArmazem = computed(() => user.value?.role === 'armazem');
    const isSolicitante = computed(() => user.value?.role === 'solicitante');

    // Configurar axios para incluir o token em todas as requisições
    const setupAxiosInterceptors = () => {
        // Evitar configurar múltiplas vezes
        if (interceptorsConfigured) return;
        interceptorsConfigured = true;

        axios.interceptors.request.use(
            (config) => {
                if (token.value) {
                    config.headers.Authorization = `Bearer ${token.value}`;
                }
                return config;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    // Token inválido ou expirado - limpar dados locais
                    token.value = null;
                    user.value = null;
                    localStorage.removeItem(TOKEN_KEY);
                    localStorage.removeItem(USER_KEY);
                    
                    // Redirecionar para login se não estiver já na página de login
                    if (window.location.pathname !== '/auth/login') {
                        window.location.href = '/auth/login';
                    }
                }
                return Promise.reject(error);
            }
        );
    };

    const login = async (email, password) => {
        try {
            const response = await axios.post('/api/auth/login', {
                email,
                password
            });

            token.value = response.data.token;
            user.value = response.data.user;

            // Salvar no localStorage
            localStorage.setItem(TOKEN_KEY, token.value);
            localStorage.setItem(USER_KEY, JSON.stringify(user.value));

            // Configurar axios
            setupAxiosInterceptors();

            return { success: true, user: user.value };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Erro ao fazer login'
            };
        }
    };

    const logout = async () => {
        try {
            if (token.value) {
                await axios.post('/api/auth/logout');
            }
        } catch (error) {
            console.error('Erro ao fazer logout:', error);
        } finally {
            // Limpar dados locais
            token.value = null;
            user.value = null;
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
        }
    };

    const fetchUser = async () => {
        try {
            const response = await axios.get('/api/auth/me');
            user.value = response.data.user;
            localStorage.setItem(USER_KEY, JSON.stringify(user.value));
            return user.value;
        } catch (error) {
            console.error('Erro ao buscar usuário:', error);
            logout();
            return null;
        }
    };

    // Configurar interceptors se já houver um token
    // Configurar interceptors se já houver um token
    setupAxiosInterceptors();

    return {
        token,
        user,
        isAuthenticated,
        isAdmin,
        isGestor,
        isArmazem,
        isSolicitante,
        login,
        logout,
        fetchUser,
        setupAxiosInterceptors
    };
}
