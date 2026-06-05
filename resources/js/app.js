import './bootstrap';
import $ from 'jquery';
import { initBurger }                    from './burger.js';
import { initToast, initConfirmDialogs } from './alerts.js';

document.addEventListener('DOMContentLoaded', () => {
    initBurger();
    initToast();
    initConfirmDialogs();

    // ── Restore dropdown states ───────────────────────────────
    $('.nav-dropdown').each(function () {
        const id = $(this).find('.nav-dropdown-trigger span').text().trim();
        if (sessionStorage.getItem('dropdown_' + id) === 'open') {
            $(this).addClass('open');
        }
    });

    // ── Save state on toggle ──────────────────────────────────
    $('.nav-dropdown-trigger').on('click', function () {
        const dropdown = $(this).closest('.nav-dropdown');
        const id = $(this).find('span').text().trim();

        dropdown.toggleClass('open');

        if (dropdown.hasClass('open')) {
            sessionStorage.setItem('dropdown_' + id, 'open');
        } else {
            sessionStorage.removeItem('dropdown_' + id);
        }
    });
});