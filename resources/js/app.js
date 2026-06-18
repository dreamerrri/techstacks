import './bootstrap';
import $ from 'jquery';
import { initBurger }                    from './burger.js';
import { initToast, initConfirmDialogs } from './alerts.js';
import 'flyonui/flyonui';

document.addEventListener('DOMContentLoaded', () => {
    initBurger();
    initToast();
    initConfirmDialogs();

    // ── Dropdown state: already restored + animated correctly by inline
    //    <head> script. Here we just handle saving state on toggle. ─────
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

    // Restore state (class already applied in <head> to prevent flash,
    // this just syncs the arrow icon)
    if (sessionStorage.getItem(SIDEBAR_KEY) === '1') {
        setSidebar(true);
    }

    // Remove pre-collapsed class so transitions work normally after load
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('sidebar-pre-collapsed');
        });
    });

    // Toggle on click
    $('#sidebar-toggle').on('click', function () {
        const isCollapsed = $layout.hasClass('sidebar-collapsed');
        setSidebar(!isCollapsed);
    });
});