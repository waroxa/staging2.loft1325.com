(function () {
  "use strict";

  var headerLanguageToggle = document.getElementById("headerLanguageToggle");
  var openMenu = document.getElementById("openMenu");
  var mobileMenu = document.getElementById("mobileMenu");
  var closeMenu = document.getElementById("closeMenu");

  function getLanguageUrl(targetLanguage) {
    var switcherLinks = document.querySelectorAll("#trp-floater-ls-language-list a[href], .trp-language-switcher-container a[href]");
    for (var i = 0; i < switcherLinks.length; i++) {
      var href = switcherLinks[i].getAttribute("href");
      if (!href || href === "#") {
        continue;
      }

      try {
        var url = new URL(href, window.location.origin);
        var pathSegments = url.pathname.replace(/^\/+/, "").split("/");
        var firstSegment = (pathSegments[0] || "").toLowerCase();

        if (targetLanguage === "en" && firstSegment === "en") {
          return url.toString();
        }

        if (targetLanguage === "fr" && firstSegment !== "en") {
          return url.toString();
        }
      } catch (error) {
        continue;
      }
    }

    var fallbackUrl = new URL(window.location.href);
    var segments = fallbackUrl.pathname.replace(/^\/+/, "").split("/").filter(Boolean);

    if (targetLanguage === "en") {
      if (segments[0] !== "en") {
        segments.unshift("en");
      }
    } else if (segments[0] === "en") {
      segments.shift();
    }

    fallbackUrl.pathname = "/" + segments.join("/") + (segments.length ? "/" : "");

    return fallbackUrl.toString();
  }

  if (headerLanguageToggle) {
    headerLanguageToggle.addEventListener("click", function () {
      var language = document.documentElement.lang === "en" ? "en" : "fr";
      var targetLanguage = language === "en" ? "fr" : "en";
      window.location.href = getLanguageUrl(targetLanguage);
    });
  }

  function openMenuPanel() {
    if (!mobileMenu) {
      return;
    }
    mobileMenu.classList.add("is-open");
    mobileMenu.setAttribute("aria-hidden", "false");
  }

  function closeMenuPanel() {
    if (!mobileMenu) {
      return;
    }
    mobileMenu.classList.remove("is-open");
    mobileMenu.setAttribute("aria-hidden", "true");
  }

  if (openMenu) {
    openMenu.addEventListener("click", openMenuPanel);
  }

  if (closeMenu) {
    closeMenu.addEventListener("click", closeMenuPanel);
  }

  if (mobileMenu) {
    mobileMenu.addEventListener("click", function (event) {
      if (event.target === mobileMenu) {
        closeMenuPanel();
      }
    });

    mobileMenu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closeMenuPanel);
    });
  }
})();
