document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;
    var revealItems = document.querySelectorAll('[data-reveal]');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
