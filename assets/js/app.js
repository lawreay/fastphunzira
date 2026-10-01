document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;
    var revealItems = document.querySelectorAll('[data-reveal]');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var siteNav = document.querySelector('.site-nav');
    var menuToggle = document.querySelector('.menu-toggle');
    var navigationMenu = document.querySelector('#site-navigation-menu');

    if (siteNav && menuToggle && navigationMenu) {
        var setMenuState = function (isOpen) {
            siteNav.classList.toggle('menu-open', isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (window.innerWidth <= 600) {
                navigationMenu.style.display = isOpen ? 'flex' : 'none';
            } else {
                navigationMenu.style.display = '';
            }
        };

        var closeMenu = function () {
            setMenuState(false);
        };

        closeMenu();

        menuToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var isOpen = !siteNav.classList.contains('menu-open');
            setMenuState(isOpen);
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
                navigationMenu.style.display = '';
                closeMenu();
            } else if (!siteNav.classList.contains('menu-open')) {
                navigationMenu.style.display = 'none';
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
