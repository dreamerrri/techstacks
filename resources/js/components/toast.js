import { toast as sonner } from 'sonner';

/**
 * Legacy-compatible toast API backed by sonner.
 * Usage: toast('success', 'Saved!') | toast('error', 'Oops')
 */
export function toast(type, message) {
    const fn = sonner[type];
    if (typeof fn === 'function') {
        fn(message);
    } else {
        sonner(message);
    }
}

export default toast;
