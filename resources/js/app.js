import './bootstrap';
import $ from 'jquery';
import { initBurger } from './burger.js';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
import 'flyonui/flyonui';

// ── Global Notyf instance ─────────────────────────────────────
window.notyf = new Notyf({
    duration: 5000,
    position: { x: 'right', y: 'top' },
    dismissible: true,
    types: [
        {
            type: 'warning',
            background: 'var(--color-warning)',
            icon: { className: 'fas fa-exclamation-triangle', tagName: 'i', color: 'white' },
        },
        {
            type: 'info',
            background: 'var(--color-info)',
            icon: { className: 'fas fa-info-circle', tagName: 'i', color: 'white' },
        },
    ],
});

document.addEventListener('DOMContentLoaded', () => {
    initBurger();

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

    // ── Dropdown state persistence ────────────────────────────
    $('.nav-dropdown-trigger').on('click', function () {
        const dropdown = $(this).closest('.nav-dropdown');
        const id       = $(this).find('span').text().trim();

        dropdown.toggleClass('open');

        if (dropdown.hasClass('open')) {
            sessionStorage.setItem('dropdown_' + id, 'open');
        } else {
            sessionStorage.removeItem('dropdown_' + id);
        }
    });

    // ── Desktop sidebar toggle ────────────────────────────────
    const SIDEBAR_KEY = 'sidebar_collapsed';
    const $layout     = $('.desktop-layout');
    const $arrow      = $('#sidebar-arrow');

    function setSidebar(collapsed) {
        if (collapsed) {
            $layout.addClass('sidebar-collapsed');
            $arrow.removeClass('fa-chevron-left').addClass('fa-chevron-right');
            sessionStorage.setItem(SIDEBAR_KEY, '1');
        } else {
            $layout.removeClass('sidebar-collapsed');
            $arrow.removeClass('fa-chevron-right').addClass('fa-chevron-left');
            sessionStorage.removeItem(SIDEBAR_KEY);
        }
    }

    if (sessionStorage.getItem(SIDEBAR_KEY) === '1') {
        setSidebar(true);
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('sidebar-pre-collapsed');
        });
    });

    $('#sidebar-toggle').on('click', function () {
        const isCollapsed = $layout.hasClass('sidebar-collapsed');
        setSidebar(!isCollapsed);
    });
});