/**
 * Loft1325 Mobile Experience - Main JavaScript
 */

(function() {
    'use strict';

    // Mobile Menu Toggle
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuClose = document.getElementById('mobile-menu-close');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (menuClose) {
        menuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = 'auto';
        });
    }

    // Close menu when clicking outside
    if (mobileMenu) {
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Date Picker Functionality
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            updateBookingSummary();
        });
    });

    // Guest Count Functionality
    const guestSelects = document.querySelectorAll('select[name*="guest"], select[name*="voyageur"]');
    guestSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            updateBookingSummary();
        });
    });

    // Update Booking Summary
    function updateBookingSummary() {
        const checkInDate = document.querySelector('input[name="check_in"], input[name="check-in"]');
        const checkOutDate = document.querySelector('input[name="check_out"], input[name="check-out"]');
        const guestCount = document.querySelector('select[name*="guest"], select[name*="voyageur"]');

        if (checkInDate && checkOutDate && guestCount) {
            const summary = document.querySelector('.booking-summary');
            if (summary) {
                const dateText = checkInDate.value && checkOutDate.value 
                    ? `${checkInDate.value} - ${checkOutDate.value}` 
                    : 'Sélectionnez vos dates';
                const guestText = guestCount.value ? `${guestCount.value} voyageur(s)` : '0 voyageur';
                
                // Update the summary display (implementation depends on your HTML structure)
                console.log('Booking Updated:', dateText, guestText);
            }
        }
    }

    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    });

    // Smooth Scrolling
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Image Gallery Swipe Support
    const galleries = document.querySelectorAll('.image-gallery');
    galleries.forEach(function(gallery) {
        let startX = 0;
        let currentX = 0;

        gallery.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
        });

        gallery.addEventListener('touchmove', function(e) {
            currentX = e.touches[0].clientX;
        });

        gallery.addEventListener('touchend', function() {
            const diff = startX - currentX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    // Swipe left - scroll right
                    gallery.scrollLeft += 100;
                } else {
                    // Swipe right - scroll left
                    gallery.scrollLeft -= 100;
                }
            }
        });
    });

    // Language Toggle
    const languageLinks = document.querySelectorAll('.language-toggle a');
    languageLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('href').split('lang=')[1];
            // Implement language switching logic here
            console.log('Switching to language:', lang);
        });
    });

    console.log('Loft1325 Mobile Experience loaded successfully.');
})();
