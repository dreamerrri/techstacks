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


// ── Desktop sidebar toggle ────────────────────────────────
const SIDEBAR_KEY = 'sidebar_collapsed';
const $layout = $('.desktop-layout');
const $arrow  = $('#sidebar-arrow'); // the <i> tag

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

// Restore on load
if (sessionStorage.getItem(SIDEBAR_KEY) === '1') {
    setSidebar(true);
}

$('#sidebar-toggle').on('click', function () {
    const isCollapsed = $layout.hasClass('sidebar-collapsed');
    setSidebar(!isCollapsed);
});
});