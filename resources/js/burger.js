export function initBurger() {
    const burger   = document.getElementById('burgerBtn');
    const dropdown = document.getElementById('burgerDropdown');
    const icon     = document.getElementById('burgerIcon');

    if (!burger || !dropdown || !icon) return;

    function close() {
        dropdown.classList.remove('open');
        icon.className = 'fas fa-bars';
        burger.setAttribute('aria-expanded', 'false');
    }

    burger.addEventListener('click', function (e) {
        e.stopPropagation();
        const opening = !dropdown.classList.contains('open');
        opening ? dropdown.classList.add('open') : close();
        icon.className = opening ? 'fas fa-times' : 'fas fa-bars';
        burger.setAttribute('aria-expanded', String(opening));
    });

    document.addEventListener('click', close);
    dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
}