(function () {
	"use strict";

	var headerLanguageToggle = document.getElementById("headerLanguageToggle");
	var openMenu = document.getElementById("openMenu");
	var mobileMenu = document.getElementById("mobileMenu");
	var closeMenu = document.getElementById("closeMenu");

	function switchLanguage() {
		var url = new URL(window.location.href);
		var segments = url.pathname.replace(/^\/+/, "").split("/").filter(Boolean);
		var isEnglish = segments[0] === "en";

		if (isEnglish) {
			segments.shift();
		} else {
			segments.unshift("en");
		}

		url.pathname = "/" + segments.join("/") + (segments.length ? "/" : "");
		window.location.href = url.toString();
	}

	if (headerLanguageToggle) {
		headerLanguageToggle.addEventListener("click", switchLanguage);
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
	}
})();
