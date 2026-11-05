// Webblog — interactions légères : thème, langue, menu mobile et confirmations.
(function () {
    'use strict';

    // Thème sombre / clair, mémorisé dans le navigateur.
    var root = document.documentElement;
    var toggle = document.querySelector('[data-theme-toggle]');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            localStorage.setItem('webblog-theme', next);
        });
    }

    // Changement de langue : redirection dans le même onglet.
    var lang = document.querySelector('[data-lang-select]');
    if (lang) {
        lang.addEventListener('change', function () {
            if (lang.value) window.location.href = lang.value;
        });
    }

    // Menu mobile.
    var navToggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Confirmation avant les suppressions.
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // L'administration utilise le thème sombre par défaut, comme les captures.
    if (document.body.classList.contains('admin') && !localStorage.getItem('webblog-theme')) {
        root.dataset.theme = 'dark';
    }
})();
