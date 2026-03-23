<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Home',
        items: [{ label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/admin' }]
    },
    {
        label: 'Stock',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-chart-line', to: '/admin/stock/dashboard' },
            { label: 'Workflow', icon: 'pi pi-fw pi-th-large', to: '/admin/stock/workflow' },
            { label: 'Itens', icon: 'pi pi-fw pi-box', to: '/admin/stock/items' },
            { label: 'Solicitações', icon: 'pi pi-fw pi-file', to: '/admin/stock/requests' }
        ]
    },
    {
        label: 'Administração',
        items: [
            { label: 'Usuários', icon: 'pi pi-fw pi-users', to: '/admin/users' }
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
