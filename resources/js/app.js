import './bootstrap';
import $ from 'jquery';
import { initBurger } from './burger.js';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import 'flyonui/flyonui';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

// ── Make Swal globally available ───────────────────────────────
window.Swal = Swal;

window.Alpine = Alpine;
Alpine.start();

// ── Global Notyf instance ─────────────────────────────────────
window.notyf = new Notyf({
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

// ── Global confirmation modal ─────────────────────────────────
window.confirmAction = function({ url, method = 'POST', csrfToken, title, text, confirmText = 'Yes, proceed', successKey = 'notyf_success' }) {
    Swal.fire({
        title: title ?? 'Are you sure?',
        text:  text  ?? 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  confirmText,
        cancelButtonText:   'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(url, {
            method,
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  csrfToken,
                'Accept':        'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                sessionStorage.setItem(successKey, data.message);
                location.reload();
            } else {
                window.notyf.error(data.message ?? 'Something went wrong.');
            }
        })
        .catch(() => window.notyf.error('Something went wrong.'));
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initBurger();
    

    // ── Session flash notifications (survive page reloads) ────
    const flashTypes = ['success', 'error', 'warning', 'info'];
    flashTypes.forEach(type => {
        const msg = sessionStorage.getItem(`notyf_${type}`);
        if (msg) {
            sessionStorage.removeItem(`notyf_${type}`);
            window.notyf[type]?.(msg) ?? window.notyf.open({ type, message: msg });
        }
    });

    // ── data-confirm forms → SweetAlert2 ─────────────────────
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = form.dataset.confirm      || 'Are you sure you want to proceed?';
            const title   = form.dataset.confirmTitle || 'Confirm Action';
            const icon    = form.dataset.confirmIcon  || 'warning';
            const btnText = form.dataset.confirmBtn   || 'Yes, proceed';

            Swal.fire({
                title, text: message, icon,
                showCancelButton:   true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor:  '#6b7280',
                confirmButtonText:  btnText,
                cancelButtonText:   'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // ── Dropdown state persistence (accordion only — mini mode hides
    //    the trigger entirely via CSS, so this only runs at full width) ──
    const $sidebar = $('#main-sidebar');

    $('.nav-dropdown-trigger').on('click', function (e) {
        const dropdown = $(this).closest('.nav-dropdown');
        const id       = $(this).find('span').text().trim();

        dropdown.toggleClass('open');

        if (dropdown.hasClass('open')) {
            sessionStorage.setItem('dropdown_' + id, 'open');
        } else {
            sessionStorage.removeItem('dropdown_' + id);
        }
    });

    // ── Desktop sidebar minify toggle (FlyonUI overlay minifier) ──
    const SIDEBAR_KEY = 'sidebar_collapsed';
    const $layout = $('.app-shell');

    function syncSidebarUI(isMinified) {
        $layout.toggleClass('sidebar-mini', isMinified);
        if (isMinified) {
            sessionStorage.setItem(SIDEBAR_KEY, '1');
        } else {
            sessionStorage.removeItem(SIDEBAR_KEY);
        }
    }

    // Restore minified state on load. We apply the same classes FlyonUI's
    // own minify() would apply directly, rather than calling the HSOverlay
    // static API - its collection is only populated on window 'load',
    // which fires after DOMContentLoaded, so the static call would no-op.
    if (sessionStorage.getItem(SIDEBAR_KEY) === '1' && $sidebar.length) {
        $sidebar.addClass('minified');
        document.body.classList.add('overlay-minified');
        syncSidebarUI(true);
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('sidebar-pre-collapsed');
        });
    });

    // FlyonUI dispatches this custom event on the sidebar element itself
    // whenever the minifier button (data-overlay-minifier) is clicked.
    // Note: HSOverlay's dispatch() nests the payload under e.detail.payload.
    document.getElementById('main-sidebar')?.addEventListener('toggleMinifierClicked.overlay', function (e) {
        syncSidebarUI(!!e.detail?.payload?.isMinified);
    });
});