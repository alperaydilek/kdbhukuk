/* ==========================================================================
   KDB HUKUK — Animations
   IntersectionObserver tabanlı reveal animasyonları
   ========================================================================== */

(function () {
  "use strict";

  const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const revealables = document.querySelectorAll(".reveal");

  if (!revealables.length) return;

  // Hareket azaltma tercihinde veya eski tarayıcıda: hepsini görünür yap
  if (prefersReduced || !("IntersectionObserver" in window)) {
    revealables.forEach(function (el) {
      el.classList.add("is-visible");
    });
    return;
  }

  const observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;

        const el = entry.target;
        const delay = el.getAttribute("data-reveal-delay");

        if (delay) {
          el.style.setProperty("--reveal-delay", delay + "ms");
        }

        el.classList.add("is-visible");
        observer.unobserve(el);
      });
    },
    {
      threshold: 0.12,
      rootMargin: "0px 0px -8% 0px"
    }
  );

  revealables.forEach(function (el) {
    observer.observe(el);
  });
})();
