import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { Ziggy } from './ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const loadingRoot = document.getElementById('app-loading-logo');
const loadingOverlay = document.getElementById('app-loading');
const loadingApp = loadingRoot
    ? createApp({
          render: () => h(AppLogoIcon),
      }).mount(loadingRoot)
    : null;

const hideInitialLoading = () => {
    if (!loadingOverlay) {
        return;
    }

    loadingOverlay.classList.add('is-hidden');
    window.setTimeout(() => {
        loadingApp?.$el?.remove?.();
        loadingOverlay.remove();
    }, 420);
};

setTimeout(hideInitialLoading, 5000);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el ?? '');

        hideInitialLoading();
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
