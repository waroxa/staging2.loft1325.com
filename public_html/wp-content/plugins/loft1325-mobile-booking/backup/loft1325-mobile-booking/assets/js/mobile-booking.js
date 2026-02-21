(function () {
	"use strict";

	var openMenu = document.getElementById("openMenu");
	var openMenuRight = document.getElementById("openMenuRight");
	var mobileMenu = document.getElementById("mobileMenu");
	var closeMenu = document.getElementById("closeMenu");

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

	if (openMenuRight) {
		openMenuRight.addEventListener("click", openMenuPanel);
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
