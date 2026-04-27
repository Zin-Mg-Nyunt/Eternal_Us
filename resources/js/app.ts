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
const isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined';
const loadingRoot = isBrowser
    ? document.getElementById('app-loading-logo')
    : null;

const loadingApp = loadingRoot
    ? createApp({
          render: () => h(AppLogoIcon),
      }).mount(loadingRoot)
    : null;

const hideInitialLoading = () => {
    if (!isBrowser) return;

    const loadingOverlay = document.getElementById('app-loading');

    if (!loadingOverlay) return;

    // CSS class ကို အားမကိုးတော့ဘဲ JS နဲ့ အတင်းဖျောက်မယ်
    loadingOverlay.style.display = 'none';
    loadingOverlay.style.opacity = '0';
    loadingOverlay.style.visibility = 'hidden';

    // ၄။ Logo Animation ကိုပါ ရပ်ပြီး ဖျက်မယ်
    if (loadingApp) {
        loadingApp?.$el?.remove?.();
    }

    // ၅။ DOM ထဲကပါ လုံးဝ ဖြုတ်ချပစ်မယ်
    window.setTimeout(() => {
        if (loadingOverlay.parentNode) {
            loadingOverlay.parentNode.removeChild(loadingOverlay);
        }
    }, 420);
};

if (isBrowser) {
    // Browser lifecycle events အတွက် fallback hide
    window.addEventListener('load', hideInitialLoading, { once: true });
    window.addEventListener('error', hideInitialLoading, { once: true });
}

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

        // Inertia/Vue app mount ပြီးချိန်မှာ ချက်ချင်း loading ဖြုတ်
        hideInitialLoading();
    },
    progress: {
        color: '#4B5563',
    },
});

if (isBrowser) {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
