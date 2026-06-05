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
        $layout.css('grid-template-columns', '0px 1fr');
        $arrow.removeClass('fa-chevron-left').addClass('fa-chevron-right');
        $('#sidebar-toggle').css('left', '0px');
        sessionStorage.setItem(SIDEBAR_KEY, '1');
    } else {
        $layout.css('grid-template-columns', '250px 1fr');
        $arrow.removeClass('fa-chevron-right').addClass('fa-chevron-left');
        $('#sidebar-toggle').css('left', '250px');
        sessionStorage.removeItem(SIDEBAR_KEY);
    }
}

// Restore on load
if (sessionStorage.getItem(SIDEBAR_KEY) === '1') {
    setSidebar(true);
}

$('#sidebar-toggle').on('click', function () {
    const isCollapsed = $layout.css('grid-template-columns').startsWith('0');
    setSidebar(!isCollapsed);
});
});