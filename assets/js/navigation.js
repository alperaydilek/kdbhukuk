/* ==========================================================================
   KDB HUKUK — Navigation
   Sticky header, mobil overlay menü, klavye erişilebilirliği
   ========================================================================== */

(function () {
  "use strict";

  /* ------------------------------------------------------------------
     Sticky header: scroll durumuna göre yüzey + akıllı gizle/göster
     ------------------------------------------------------------------ */
  const header = document.querySelector(".site-header");

  if (header) {
    let lastY = window.scrollY;
    let ticking = false;

    const update = function () {
      const y = window.scrollY;

      header.classList.toggle("is-scrolled", y > 24);

      // Aşağı inerken gizle, yukarı çıkarken göster (menü açıkken dokunma)
      if (!document.body.classList.contains("nav-locked")) {
        if (y > lastY && y > 320) {
          header.classList.add("is-hidden");
        } else {
          header.classList.remove("is-hidden");
        }
      }

      lastY = y;
      ticking = false;
    };

    window.addEventListener(
      "scroll",
      function () {
        if (!ticking) {
          window.requestAnimationFrame(update);
          ticking = true;
        }
      },
      { passive: true }
    );

    update();
  }

  /* ------------------------------------------------------------------
     Mobil menü (fullscreen overlay)
     ------------------------------------------------------------------ */
  const toggle = document.querySelector(".nav-toggle");
  const mobileNav = document.getElementById("mobile-nav");

  if (toggle && mobileNav) {
    const focusableSelector = "a[href], button:not([disabled])";
    let lastFocused = null;

    const openNav = function () {
      lastFocused = document.activeElement;
      toggle.setAttribute("aria-expanded", "true");
      toggle.setAttribute("aria-label", "Menüyü kapat");
      mobileNav.classList.add("is-open");
      mobileNav.removeAttribute("aria-hidden");
      document.body.classList.add("nav-locked");
      if (header) header.classList.remove("is-hidden");

      const first = mobileNav.querySelector(focusableSelector);
      if (first) first.focus();
    };

    const closeNav = function () {
      toggle.setAttribute("aria-expanded", "false");
      toggle.setAttribute("aria-label", "Menüyü aç");
      mobileNav.classList.remove("is-open");
      mobileNav.setAttribute("aria-hidden", "true");
      document.body.classList.remove("nav-locked");
      if (lastFocused) lastFocused.focus();
    };

    toggle.addEventListener("click", function () {
      const isOpen = toggle.getAttribute("aria-expanded") === "true";
      if (isOpen) {
        closeNav();
      } else {
        openNav();
      }
    });

    // Escape ile kapat
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && mobileNav.classList.contains("is-open")) {
        closeNav();
      }
    });

    // Menü içindeki linke tıklanınca kapat
    mobileNav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closeNav);
    });

    // Basit focus trap
    mobileNav.addEventListener("keydown", function (event) {
      if (event.key !== "Tab") return;

      const focusables = [toggle].concat(
        Array.prototype.slice.call(mobileNav.querySelectorAll(focusableSelector))
      );
      const first = focusables[0];
      const last = focusables[focusables.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
  }
})();
