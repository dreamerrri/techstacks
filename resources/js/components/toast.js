import { toast as sonner } from 'sonner';

/**
 * Legacy-compatible API backed by sonner (shadcn-style toasts).
 * Theme-reactive: colors come from FlyonUI CSS variables, so popups
 * automatically match whichever data-theme is active.
 */
export function toast(type = 'success', message) {
    if (!message) return;
    const fn = sonner[type];
    if (typeof fn === 'function') {
        fn(message);
    } else {
        sonner(message);
    }
}

export default toast;
