import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3' // Importamos Link y Head
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue) // Usar Ziggy como plugin es más limpio
      .component('Link', Link) // REGISTRO CORRECTO: Como componente global
      .component('Head', Head) // Opcional pero recomendado
      .mount(el)
  },
})