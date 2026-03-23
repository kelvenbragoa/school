import AppLayout from '@/layout/AppLayout.vue';
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/admin',
            component: AppLayout,
            children: [
                {
                    path: '/admin',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue')
                },
                {
                    path: '/uikit/formlayout',
                    name: 'formlayout',
                    component: () => import('@/views/uikit/FormLayout.vue')
                },
                {
                    path: '/uikit/input',
                    name: 'input',
                    component: () => import('@/views/uikit/InputDoc.vue')
                },
                {
                    path: '/uikit/button',
                    name: 'button',
                    component: () => import('@/views/uikit/ButtonDoc.vue')
                },
                {
                    path: '/uikit/table',
                    name: 'table',
                    component: () => import('@/views/uikit/TableDoc.vue')
                },
                {
                    path: '/uikit/list',
                    name: 'list',
                    component: () => import('@/views/uikit/ListDoc.vue')
                },
                {
                    path: '/uikit/tree',
                    name: 'tree',
                    component: () => import('@/views/uikit/TreeDoc.vue')
                },
                {
                    path: '/uikit/panel',
                    name: 'panel',
                    component: () => import('@/views/uikit/PanelsDoc.vue')
                },

                {
                    path: '/uikit/overlay',
                    name: 'overlay',
                    component: () => import('@/views/uikit/OverlayDoc.vue')
                },
                {
                    path: '/uikit/media',
                    name: 'media',
                    component: () => import('@/views/uikit/MediaDoc.vue')
                },
                {
                    path: '/uikit/message',
                    name: 'message',
                    component: () => import('@/views/uikit/MessagesDoc.vue')
                },
                {
                    path: '/uikit/file',
                    name: 'file',
                    component: () => import('@/views/uikit/FileDoc.vue')
                },
                {
                    path: '/uikit/menu',
                    name: 'menu',
                    component: () => import('@/views/uikit/MenuDoc.vue')
                },
                {
                    path: '/uikit/charts',
                    name: 'charts',
                    component: () => import('@/views/uikit/ChartDoc.vue')
                },
                {
                    path: '/uikit/misc',
                    name: 'misc',
                    component: () => import('@/views/uikit/MiscDoc.vue')
                },
                {
                    path: '/uikit/timeline',
                    name: 'timeline',
                    component: () => import('@/views/uikit/TimelineDoc.vue')
                },
                {
                    path: '/pages/empty',
                    name: 'empty',
                    component: () => import('@/views/pages/Empty.vue')
                },
                {
                    path: '/pages/crud',
                    name: 'crud',
                    component: () => import('@/views/pages/Crud.vue')
                },
                {
                    path: '/documentation',
                    name: 'documentation',
                    component: () => import('@/views/pages/Documentation.vue')
                },
                // Stock System Routes
                {
                    path: '/admin/stock/dashboard',
                    name: 'stock.dashboard',
                    component: () => import('@/views/pages/stock/dashboard/Index.vue')
                },
                {
                    path: '/admin/stock/workflow',
                    name: 'stock.workflow',
                    component: () => import('@/views/pages/stock/workflow/Index.vue')
                },
                {
                    path: '/admin/stock/items',
                    name: 'stock.items.index',
                    component: () => import('@/views/pages/stock/items/Index.vue')
                },
                {
                    path: '/admin/stock/items/create',
                    name: 'stock.items.create',
                    component: () => import('@/views/pages/stock/items/Create.vue')
                },
                {
                    path: '/admin/stock/items/:id',
                    name: 'stock.items.show',
                    component: () => import('@/views/pages/stock/items/Show.vue')
                },
                {
                    path: '/admin/stock/items/:id/edit',
                    name: 'stock.items.edit',
                    component: () => import('@/views/pages/stock/items/Edit.vue')
                },
                {
                    path: '/admin/stock/requests',
                    name: 'stock.requests.index',
                    component: () => import('@/views/pages/stock/requests/Index.vue')
                },
                {
                    path: '/admin/stock/requests/create',
                    name: 'stock.requests.create',
                    component: () => import('@/views/pages/stock/requests/Create.vue')
                },
                {
                    path: '/admin/stock/requests/:id',
                    name: 'stock.requests.show',
                    component: () => import('@/views/pages/stock/requests/Show.vue')
                },
                {
                    path: '/admin/stock/requests/:id/edit',
                    name: 'stock.requests.edit',
                    component: () => import('@/views/pages/stock/requests/Create.vue')
                },
                // Users Routes
                {
                    path: '/admin/users',
                    name: 'users.index',
                    component: () => import('@/views/pages/users/Index.vue')
                },
                {
                    path: '/admin/users/create',
                    name: 'users.create',
                    component: () => import('@/views/pages/users/Create.vue')
                },
                {
                    path: '/admin/users/:id',
                    name: 'users.show',
                    component: () => import('@/views/pages/users/Show.vue')
                },
                {
                    path: '/admin/users/:id/edit',
                    name: 'users.edit',
                    component: () => import('@/views/pages/users/Edit.vue')
                }
            ]
        },
        {
            path: '/',
            name: 'landing',
            component: () => import('@/views/pages/Landing.vue')
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/views/pages/NotFound.vue')
        },

        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/views/pages/auth/Login.vue')
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/views/pages/auth/Access.vue')
        },
        {
            path: '/auth/error',
            name: 'error',
            component: () => import('@/views/pages/auth/Error.vue')
        }
    ]
});

export default router;
