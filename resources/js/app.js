import './bootstrap';
import '../css/app.css';
import Myapplayout from '@/Layouts/Myapplayout.vue';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import vClickOutside from 'click-outside-vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // app.config.globalProperties.$route = route
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue)
            .use(ZiggyVue, Ziggy)
            .use(ToastService)
            .directive('clickOutside',vClickOutside.directive)
            .mount(el)
            
    },
    progress: {
        color: '#4B5563',
    },
});
