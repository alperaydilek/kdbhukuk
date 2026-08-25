/* ==========================================================================
   KDB HUKUK — Main
   Sayfa geçişi, akordeon, lightbox, form doğrulama, blog filtresi, TOC
   ========================================================================== */

(function () {
  "use strict";

  const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ------------------------------------------------------------------
     Sayfa geçişi (kısa veil animasyonu)
     ------------------------------------------------------------------ */
  const veil = document.getElementById("page-veil");

  if (veil && !prefersReduced) {
    document.addEventListener("click", function (event) {
      const link = event.target.closest("a[href]");
      if (!link) return;

      const href = link.getAttribute("href");
      const isInternal =
        href &&
        !href.startsWith("#") &&
        !href.startsWith("mailto:") &&
        !href.startsWith("tel:") &&
        !href.startsWith("http") &&
        link.target !== "_blank";

      if (!isInternal) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      event.preventDefault();
      veil.classList.add("is-active");

      window.setTimeout(function () {
        window.location.href = href;
      }, 280);
    });

    // bfcache'ten dönüşte veil'i temizle
    window.addEventListener("pageshow", function () {
      veil.classList.remove("is-active");
    });
  }

  /* ------------------------------------------------------------------
     Akordeon (SSS)
     ------------------------------------------------------------------ */
  document.querySelectorAll(".accordion").forEach(function (accordion) {
    accordion.querySelectorAll(".accordion__trigger").forEach(function (trigger) {
      trigger.addEventListener("click", function () {
        const expanded = trigger.getAttribute("aria-expanded") === "true";
        const panel = document.getElementById(trigger.getAttribute("aria-controls"));
        if (!panel) return;

        trigger.setAttribute("aria-expanded", expanded ? "false" : "true");
        panel.style.maxHeight = expanded ? "0px" : panel.scrollHeight + "px";
      });
    });
  });

  /* ------------------------------------------------------------------
     Lightbox (galeri)
     ------------------------------------------------------------------ */
  const lightbox = document.getElementById("lightbox");
  const galleryItems = Array.prototype.slice.call(
    document.querySelectorAll(".gallery__item")
  );

  if (lightbox && galleryItems.length) {
    const imgEl = lightbox.querySelector(".lightbox__figure img");
    const captionEl = lightbox.querySelector(".lightbox__caption");
    const btnClose = lightbox.querySelector(".lightbox__close");
    const btnPrev = lightbox.querySelector(".lightbox__prev");
    const btnNext = lightbox.querySelector(".lightbox__next");
    let currentIndex = 0;
    let lastFocused = null;

    const render = function (index) {
      currentIndex = (index + galleryItems.length) % galleryItems.length;
      const item = galleryItems[currentIndex];
      const thumb = item.querySelector("img");
      if (!thumb) return;

      imgEl.src = item.getAttribute("data-full") || thumb.currentSrc || thumb.src;
      imgEl.alt = thumb.alt || "";
      if (captionEl) {
        captionEl.textContent = item.getAttribute("data-caption") || thumb.alt || "";
      }
    };

    const openLightbox = function (index) {
      lastFocused = document.activeElement;
      render(index);
      lightbox.classList.add("is-open");
      lightbox.removeAttribute("aria-hidden");
      document.body.classList.add("nav-locked");
      if (btnClose) btnClose.focus();
    };

    const closeLightbox = function () {
      lightbox.classList.remove("is-open");
      lightbox.setAttribute("aria-hidden", "true");
      document.body.classList.remove("nav-locked");
      if (lastFocused) lastFocused.focus();
    };

    galleryItems.forEach(function (item, index) {
      item.addEventListener("click", function () {
        openLightbox(index);
      });
    });

    if (btnClose) btnClose.addEventListener("click", closeLightbox);
    if (btnPrev) {
      btnPrev.addEventListener("click", function () {
        render(currentIndex - 1);
      });
    }
    if (btnNext) {
      btnNext.addEventListener("click", function () {
        render(currentIndex + 1);
      });
    }

    lightbox.addEventListener("click", function (event) {
      if (event.target === lightbox) closeLightbox();
    });

    document.addEventListener("keydown", function (event) {
      if (!lightbox.classList.contains("is-open")) return;

      if (event.key === "Escape") closeLightbox();
      if (event.key === "ArrowLeft") render(currentIndex - 1);
      if (event.key === "ArrowRight") render(currentIndex + 1);
    });
  }

  /* ------------------------------------------------------------------
     İletişim formu — frontend demo doğrulama
     ------------------------------------------------------------------ */
  const form = document.getElementById("contact-form");

  if (form) {
    const statusEl = form.querySelector(".form__status");

    const setError = function (field, hasError) {
      const wrapper = field.closest(".form__field") || field.closest(".form__consent");
      if (!wrapper) return;
      wrapper.classList.toggle("has-error", hasError);
      field.setAttribute("aria-invalid", hasError ? "true" : "false");
    };

    const validators = {
      name: function (value) {
        return value.trim().length >= 3;
      },
      phone: function (value) {
        return /^[+()\d\s-]{10,17}$/.test(value.trim());
      },
      email: function (value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value.trim());
      },
      subject: function (value) {
        return value.trim().length > 0;
      },
      message: function (value) {
        return value.trim().length >= 20;
      }
    };

    form.addEventListener("submit", function (event) {
      event.preventDefault();

      let valid = true;
      let firstInvalid = null;

      Object.keys(validators).forEach(function (name) {
        const field = form.elements[name];
        if (!field) return;

        const ok = validators[name](field.value);
        setError(field, !ok);

        if (!ok) {
          valid = false;
          if (!firstInvalid) firstInvalid = field;
        }
      });

      const consent = form.elements.kvkk;
      if (consent) {
        const ok = consent.checked;
        setError(consent, !ok);
        if (!ok) {
          valid = false;
          if (!firstInvalid) firstInvalid = consent;
        }
      }

      if (!valid) {
        if (statusEl) {
          statusEl.textContent = "Lütfen işaretli alanları kontrol ediniz.";
          statusEl.classList.add("is-error");
          statusEl.classList.remove("is-success");
        }
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      // Frontend demo: gerçek gönderim backend aşamasında eklenecek
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      if (statusEl) {
        statusEl.textContent =
          "Mesajınız alındı. En kısa sürede sizinle iletişime geçilecektir. (Demo — gerçek gönderim backend aşamasında etkinleşecektir.)";
        statusEl.classList.add("is-success");
        statusEl.classList.remove("is-error");
      }

      form.reset();
      window.setTimeout(function () {
        if (submitBtn) submitBtn.disabled = false;
      }, 4000);
    });

    // Alan düzeltildiğinde hatayı kaldır
    form.addEventListener("input", function (event) {
      const field = event.target;
      const name = field.name;

      if (validators[name] && validators[name](field.value)) {
        setError(field, false);
      }
      if (name === "kvkk" && field.checked) {
        setError(field, false);
      }
    });
  }

  /* ------------------------------------------------------------------
     Blog kategori filtresi
     ------------------------------------------------------------------ */
  const filterBar = document.querySelector(".filter-bar");

  if (filterBar) {
    const buttons = filterBar.querySelectorAll(".filter-btn");
    const entries = document.querySelectorAll("[data-category]");

    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        const filter = btn.getAttribute("data-filter");

        buttons.forEach(function (other) {
          const isActive = other === btn;
          other.classList.toggle("is-active", isActive);
          other.setAttribute("aria-pressed", isActive ? "true" : "false");
        });

        entries.forEach(function (entry) {
          const match =
            filter === "all" || entry.getAttribute("data-category") === filter;
          entry.classList.toggle("is-hidden", !match);
        });
      });
    });
  }

  /* ------------------------------------------------------------------
     Legal sayfalar — TOC scrollspy
     ------------------------------------------------------------------ */
  const tocLinks = document.querySelectorAll(".legal-toc__list a");

  if (tocLinks.length && "IntersectionObserver" in window) {
    const sections = [];

    tocLinks.forEach(function (link) {
      const id = link.getAttribute("href").slice(1);
      const target = document.getElementById(id);
      if (target) sections.push({ link: link, target: target });
    });

    const spy = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;

          sections.forEach(function (item) {
            item.link.classList.toggle("is-active", item.target === entry.target);
          });
        });
      },
      { rootMargin: "-20% 0px -70% 0px" }
    );

    sections.forEach(function (item) {
      spy.observe(item.target);
    });
  }
})();
