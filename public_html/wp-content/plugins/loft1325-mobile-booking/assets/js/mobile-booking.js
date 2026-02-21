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

	function containsAny(text, words) {
		for (var i = 0; i < words.length; i++) {
			if (text.indexOf(words[i]) !== -1) return true;
		}
		return false;
	}

	function cleanupSpacerArtifacts(content) {
		if (!content) {
			return;
		}


		var blocks = content.querySelectorAll('.nd_booking_section, .elementor-widget-container > div');
		blocks.forEach(function (block) {
			if (!block || block.children.length > 0) {
				return;
			}

			var className = block.className || '';
			var text = (block.textContent || '').replace(/\u00a0/g, ' ').trim();
			var hasThinDivider = className.indexOf('nd_booking_height_2') !== -1 && className.indexOf('nd_booking_bg_grey') !== -1;
			var isHeightSpacer = /nd_booking_height_(20|30|40)/.test(className);
			if (text === '' && (hasThinDivider || isHeightSpacer)) {
				block.classList.add('loft1325-mobile-booking__trimmed-spacer');
			}
		});

		var firstChild = content.firstElementChild;
		while (firstChild && firstChild.classList.contains('loft1325-mobile-booking__trimmed-spacer')) {
			firstChild.remove();
			firstChild = content.firstElementChild;
		}
	}

	function refineBookingLayout() {
		var content = document.querySelector('.loft1325-mobile-booking__content');
		if (!content) {
			return;
		}

		cleanupSpacerArtifacts(content);

		var sections = content.querySelectorAll('.elementor-top-section');
		sections.forEach(function (section) {
			var text = (section.innerText || '').toLowerCase();
			var isHeroTitle = containsAny(text, ['réservation', 'reservation', 'checkout', 'paiement']);
			var hasHeroHeight = section.offsetHeight > 220;
			var hasBgImage = window.getComputedStyle(section).backgroundImage !== 'none';
			if (isHeroTitle && hasHeroHeight && hasBgImage) {
				section.classList.add('loft1325-mobile-booking__hidden-mobile-hero');
			}
		});

		var candidates = content.querySelectorAll('section, article, div');
		var reservationSummary = null;
		for (var i = 0; i < candidates.length; i++) {
			var block = candidates[i];
			if (block.closest('.loft1325-mobile-booking__finalize')) continue;
			var summaryText = (block.innerText || '').toLowerCase().replace(/\s+/g, ' ');
			var isSummary = summaryText.indexOf('votre réservation') !== -1 ||
				((summaryText.indexOf('arrivée') !== -1 || summaryText.indexOf('arrival') !== -1) &&
				(summaryText.indexOf('départ') !== -1 || summaryText.indexOf('departure') !== -1));
			if (isSummary && block.offsetHeight > 80) {
				reservationSummary = block;
				break;
			}
		}

		if (reservationSummary) {
			reservationSummary.classList.add('loft1325-mobile-booking__reservation-summary');
			var firstForm = content.querySelector('form, #nd_booking_container_booking_form, #nd_booking_container_checkout_form, .nd_booking_section_content_booking_form');
			if (firstForm) {
				content.insertBefore(reservationSummary, firstForm.closest('section, article, div') || firstForm);
			} else {
				content.insertBefore(reservationSummary, content.firstChild);
			}
		}
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

	document.addEventListener('DOMContentLoaded', refineBookingLayout);
})();
