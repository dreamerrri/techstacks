import './bootstrap';
import { initBurger }                    from './burger.js';
import { initToast, initConfirmDialogs } from './alerts.js';
import $ from 'jquery';
import DataTable from 'datatables.net';

document.addEventListener('DOMContentLoaded', () => {
    initBurger();
    initToast();
    initConfirmDialogs();

    // ── Dropdown state ─────────────────────────────────────────
    document.querySelectorAll('.nav-dropdown-trigger').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const dropdown = this.closest('.nav-dropdown');
            const id       = this.querySelector('span')?.textContent.trim();

            dropdown.classList.toggle('open');

            if (dropdown.classList.contains('open')) {
                sessionStorage.setItem('dropdown_' + id, 'open');
            } else {
                sessionStorage.removeItem('dropdown_' + id);
            }
        });
    });

    // ── Desktop sidebar toggle ────────────────────────────────
    const SIDEBAR_KEY = 'sidebar_collapsed';
    const layout      = document.querySelector('.desktop-layout');
    const arrow       = document.getElementById('sidebar-arrow');

    function setSidebar(collapsed) {
        if (collapsed) {
            layout.classList.add('sidebar-collapsed');
            arrow.classList.remove('fa-chevron-left');
            arrow.classList.add('fa-chevron-right');
            sessionStorage.setItem(SIDEBAR_KEY, '1');
        } else {
            layout.classList.remove('sidebar-collapsed');
            arrow.classList.remove('fa-chevron-right');
            arrow.classList.add('fa-chevron-left');
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

    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        const isCollapsed = layout.classList.contains('sidebar-collapsed');
        setSidebar(!isCollapsed);
    });
});