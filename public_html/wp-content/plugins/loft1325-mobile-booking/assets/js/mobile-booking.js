(function () {
	"use strict";

	var openMenu = document.getElementById("openMenu");
	var headerLanguageToggle = document.getElementById("headerLanguageToggle");
	var mobileMenu = document.getElementById("mobileMenu");
	var closeMenu = document.getElementById("closeMenu");

	function getLanguageUrl(targetLanguage) {
		var switcherLinks = document.querySelectorAll("#trp-floater-ls-language-list a[href], .trp-language-switcher-container a[href]");
		for (var i = 0; i < switcherLinks.length; i++) {
			var href = switcherLinks[i].getAttribute("href");
			if (!href || href === "#") continue;
			try {
				var url = new URL(href, window.location.origin);
				var first = (url.pathname.replace(/^\/+/, "").split("/")[0] || "").toLowerCase();
				if (targetLanguage === "en" && first === "en") return url.toString();
				if (targetLanguage === "fr" && first !== "en") return url.toString();
			} catch (error) {}
		}
		var fallback = new URL(window.location.href);
		var seg = fallback.pathname.replace(/^\/+/, "").split("/").filter(Boolean);
		if (targetLanguage === "en") { if (seg[0] !== "en") seg.unshift("en"); }
		else if (seg[0] === "en") { seg.shift(); }
		fallback.pathname = "/" + seg.join("/") + (seg.length ? "/" : "");
		return fallback.toString();
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

	if (headerLanguageToggle) {
		headerLanguageToggle.addEventListener("click", function () {
			var targetLanguage = document.documentElement.lang === "en" ? "fr" : "en";
			window.location.href = getLanguageUrl(targetLanguage);
		});
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
	}
})();
