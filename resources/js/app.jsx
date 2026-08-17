import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const theme =
    document.documentElement.dataset.theme ||
    window.__inertia_initial?.theme ||
    'techstacks';

document.documentElement.dataset.theme = theme;

createInertiaApp({
    title: (title) => (title ? `${title} - Techstacks Logify` : 'Techstacks Logify'),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        if (props.initialPage?.props?.auth?.user?.theme) {
            document.documentElement.dataset.theme = props.initialPage.props.auth.user.theme;
        }

        root.render(<App {...props} />);
    },
    progress: {
        delay: 250,
        color: '#24D6AE',
        includeCSS: true,
        showSpinner: true,
    },
});