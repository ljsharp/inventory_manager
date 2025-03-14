import '../css/app.css';
import '../css/styles.scss';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import Aura from '@primeuix/themes/aura';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Inventory Manager';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: 'system',
                        cssLayer: false,
                    },
                },
            })
            .use(ToastService);

        // ✅ Global `$can` function for Composition API
        app.config.globalProperties.$can = (permissions: string[]) => {
            const allPermissions = props.initialPage.props.auth.can;
            return permissions.some((permission) => allPermissions[permission]);
        };

        // ✅ Mixin for Options API
        app.mixin({
            methods: {
                can(permissions: string[]) {
                    return this.$can(permissions);
                },
            },
        });

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
