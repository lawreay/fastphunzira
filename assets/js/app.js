document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;
    var revealItems = document.querySelectorAll('[data-reveal]');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var siteNav = document.querySelector('.site-nav');
    var menuToggle = document.querySelector('.menu-toggle');
    var navigationMenu = document.querySelector('#site-navigation-menu');

    if (siteNav && menuToggle && navigationMenu) {
        var closeMenu = function () {
            siteNav.classList.remove('menu-open');
            menuToggle.setAttribute('aria-expanded', 'false');
        };

        menuToggle.addEventListener('click', function () {
            var isOpen = siteNav.classList.toggle('menu-open');
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        navigationMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('click', function (event) {
            if (!siteNav.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 600) {
                closeMenu();
            }
        });
    }

    if (revealItems.length === 0) {
        return;
    }

    root.classList.add('motion-ready');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach(function (item) {
            item.classList.add('is-visible');
        });
        return;
    }

    var revealObserver = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -40px', threshold: 0.12 });

    revealItems.forEach(function (item) {
        revealObserver.observe(item);
    });
});
