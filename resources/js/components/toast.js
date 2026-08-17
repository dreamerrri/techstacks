import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

let notyf;

function getNotyf() {
    if (!notyf) {
        notyf = new Notyf({
            duration: 5000,
            position: { x: 'right', y: 'top' },
            dismissible: true,
            types: [
                {
                    type: 'warning',
                    background: 'var(--color-warning)',
                    icon: { className: 'icon-[ph--warning-fill]', tagName: 'i', color: 'white' },
                },
                {
                    type: 'info',
                    background: 'var(--color-info)',
                    icon: { className: 'icon-[ph--info-fill]', tagName: 'i', color: 'white' },
                },
            ],
        });
    }
    return notyf;
}

export function toast(type = 'success', message) {
    if (!message) return;
    const n = getNotyf();
    if (typeof n[type] === 'function') {
        n[type](message);
    } else {
        n.open({ type, message });
    }
}

export { getNotyf };