/* PRESS — rail overflow indicator.

   A horizontal nav that scrolls has one real accessibility problem: nothing
   tells you there is more. CSS cannot detect overflow on its own, and the
   scroll-driven-animation approach that could depends on a feature whose
   fallback is ambiguous — not where an accessibility affordance belongs.

   Six lines of state instead: two boolean attributes saying which edge still
   has content behind it. They are PHYSICAL (left/right), not logical, so RTL
   needs no special case: the script reports what it measures.

   Without this script the rail still scrolls. Nothing breaks; the hint is
   simply absent. */
(function () {
    var SEL = '.fi-topbar-nav-groups';

    function update(nav) {
        var slack = nav.scrollWidth - nav.clientWidth;
        if (slack <= 1) {
            nav.removeAttribute('data-press-fade-left');
            nav.removeAttribute('data-press-fade-right');
            return;
        }
        // scrollLeft is negative in RTL in some engines; distance is what matters.
        var x = Math.abs(nav.scrollLeft);
        nav.toggleAttribute('data-press-fade-left', x > 1);
        nav.toggleAttribute('data-press-fade-right', x < slack - 1);
    }

    function attach() {
        var nav = document.querySelector(SEL);
        if (!nav || nav.dataset.pressRailBound) return;
        nav.dataset.pressRailBound = '1';
        var run = function () { update(nav); };
        nav.addEventListener('scroll', run, { passive: true });
        if (window.ResizeObserver) new ResizeObserver(run).observe(nav);
        run();
    }

    document.addEventListener('DOMContentLoaded', attach);
    // Filament panels run in SPA mode: the topbar is re-rendered on navigation.
    document.addEventListener('livewire:navigated', attach);
    attach();
})();
