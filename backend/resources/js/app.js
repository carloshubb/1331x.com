import './bootstrap';
import '../css/app.css';
import '../css/icons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import '@fortawesome/fontawesome-free/css/all.min.css';
import router from './router';
import { createHead } from "@vueuse/head";


createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)            // inertia plugin
            .use(router)            // vue-router
            .use(createHead())      // vueuse head
           
            .mount(el);
    },
});
