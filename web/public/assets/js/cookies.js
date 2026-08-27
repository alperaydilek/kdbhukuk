/* ==========================================================================
   KDB HUKUK — Cookie Banner
   LocalStorage ile tercih saklama (frontend demo)
   ========================================================================== */

(function () {
  "use strict";

  const STORAGE_KEY = "kdb-cookie-consent";
  const banner = document.getElementById("cookie-banner");

  if (!banner) return;

  const btnAccept = banner.querySelector("[data-cookie-accept]");
  const btnReject = banner.querySelector("[data-cookie-reject]");
  const btnManage = banner.querySelector("[data-cookie-manage]");
  const btnSave = banner.querySelector("[data-cookie-save]");
  const prefAnalytics = banner.querySelector("#cookie-pref-analytics");

  const readConsent = function () {
    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (error) {
      return null;
    }
  };

  const saveConsent = function (consent) {
    try {
      window.localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          necessary: true,
          analytics: Boolean(consent.analytics),
          decidedAt: new Date().toISOString()
        })
      );
    } catch (error) {
      /* localStorage kullanılamıyorsa banner her ziyarette görünür */
    }
  };

  const hideBanner = function () {
    banner.classList.remove("is-visible");
    window.setTimeout(function () {
      banner.setAttribute("hidden", "");
    }, 700);
  };

  const showBanner = function () {
    banner.removeAttribute("hidden");
    // transition'ın tetiklenmesi için bir frame bekle
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(function () {
        banner.classList.add("is-visible");
      });
    });
  };

  // Daha önce karar verilmişse banner'ı hiç gösterme
  if (readConsent()) {
    banner.setAttribute("hidden", "");
    return;
  }

  window.setTimeout(showBanner, 1200);

  if (btnAccept) {
    btnAccept.addEventListener("click", function () {
      saveConsent({ analytics: true });
      hideBanner();
    });
  }

  if (btnReject) {
    btnReject.addEventListener("click", function () {
      saveConsent({ analytics: false });
      hideBanner();
    });
  }

  if (btnManage) {
    btnManage.addEventListener("click", function () {
      const expanded = banner.classList.toggle("show-prefs");
      btnManage.setAttribute("aria-expanded", expanded ? "true" : "false");
    });
  }

  if (btnSave) {
    btnSave.addEventListener("click", function () {
      saveConsent({ analytics: prefAnalytics ? prefAnalytics.checked : false });
      hideBanner();
    });
  }
})();
