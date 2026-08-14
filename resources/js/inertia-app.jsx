import './bootstrap';
import '../css/app.css';
import React from 'react';
import { route } from 'ziggy-js';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'TechStacks';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });

const resolvePageComponent = (name) => {
    const importname = `./Pages/${name}.jsx`;
    for (const path in pages) {
        if (path === importname) {
            return pages[path].default;
        }
    }
    throw new Error(`Page not found: ${importname}`);
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(name),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
