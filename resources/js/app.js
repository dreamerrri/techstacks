import './bootstrap';
import { initBurger }                    from './burger.js';
import { initToast, initConfirmDialogs } from './alerts.js';

document.addEventListener('DOMContentLoaded', () => {
    initBurger();
    initToast();
    initConfirmDialogs();
});