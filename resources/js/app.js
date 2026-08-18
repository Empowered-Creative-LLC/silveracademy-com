import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Silver Academy';

// On 419 (CSRF/session expired), show message and reload so the user gets a fresh token
router.on('invalid', (event) => {
    const response = event.detail?.response;
    if (response?.status === 419) {
        event.preventDefault();
        const visitUrl = String(event.detail?.visit?.url || '');
        if (visitUrl.includes('/logout')) {
            window.location.href = '/login';
            return;
        }
        sessionStorage.setItem('session_expired_message', '1');
        window.location.reload();
    }
});

// Keep the portal session alive while the tab is open (school-day length sessions).
if (typeof window !== 'undefined') {
    setInterval(() => {
        if (document.visibilityState === 'hidden') {
            return;
        }
        if (!window.location.pathname.startsWith('/portal')) {
            return;
        }
        fetch('/portal/session/ping', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).catch(() => {});
    }, 5 * 60 * 1000);
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});