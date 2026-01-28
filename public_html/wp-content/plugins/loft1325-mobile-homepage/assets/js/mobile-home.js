(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
            return;
        }
        document.addEventListener('DOMContentLoaded', fn);
    }

    function initializeNav(body) {
        var nav = document.getElementById('loft1325-mobile-nav');
        var toggle = document.querySelector('[data-loft1325-mobile-nav-toggle]');
        var overlay = document.querySelector('[data-loft1325-mobile-nav-overlay]');

        if (!nav || !toggle || !overlay) {
            return;
        }

        function openNav() {
            nav.removeAttribute('hidden');
            nav.setAttribute('aria-hidden', 'false');
            body.classList.add('loft1325-mobile-home--nav-open');
            toggle.setAttribute('aria-expanded', 'true');
            overlay.removeAttribute('hidden');
        }

        function closeNav() {
            nav.setAttribute('aria-hidden', 'true');
            body.classList.remove('loft1325-mobile-home--nav-open');
            toggle.setAttribute('aria-expanded', 'false');
            overlay.setAttribute('hidden', 'hidden');
            window.setTimeout(function () {
                if (nav.getAttribute('aria-hidden') === 'true') {
                    nav.setAttribute('hidden', 'hidden');
                }
            }, 280);
        }

        function toggleNav(event) {
            event.preventDefault();
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                closeNav();
            } else {
                openNav();
            }
        }

        toggle.addEventListener('click', toggleNav);
        overlay.addEventListener('click', closeNav);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && body.classList.contains('loft1325-mobile-home--nav-open')) {
                closeNav();
            }
        });

        nav.addEventListener('click', function (event) {
            if (event.target.tagName && event.target.tagName.toLowerCase() === 'a') {
                closeNav();
            }
        });

        var observer = new MutationObserver(function () {
            if (nav.getAttribute('aria-hidden') === 'false') {
                nav.classList.add('loft1325-mobile-home__nav--open');
            } else {
                nav.classList.remove('loft1325-mobile-home__nav--open');
            }
        });

        observer.observe(nav, { attributes: true, attributeFilter: ['aria-hidden'] });
    }

    function initializeSearchForm() {
        var searchCard = document.getElementById('loft1325-mobile-home-search');
        if (!searchCard) {
            return;
        }

        var form = searchCard.querySelector('.loft-search-toolbar__form');
        if (!form) {
            return;
        }

        var checkInInput = form.querySelector('#nd_booking_archive_form_date_range_from');
        var checkOutInput = form.querySelector('#nd_booking_archive_form_date_range_to');
        var totalGuestsInput = form.querySelector('#nd_booking_archive_form_guests');
        var guestGroup = form.querySelector('[data-guest-group="total"]');
        var guestInput = guestGroup ? guestGroup.querySelector('input[type="hidden"]') : null;
        var guestValue = guestGroup ? guestGroup.querySelector('.loft-search-toolbar__guests-value') : null;
        var dateInput = form.querySelector('#loft_booking_date_range');
        var dateClear = form.querySelector('[data-date-clear]');
        var promoToggle = form.querySelector('[data-promo-toggle]');
        var promoField = form.querySelector('[data-promo-field]');
        var promoInput = form.querySelector('#loft_booking_coupon');
        var promoCheckoutInput = form.querySelector('#loft_booking_coupon_checkout');
        var submitButton = form.querySelector('.loft-search-toolbar__submit');
        var language = (form.getAttribute('data-language') || 'fr').toLowerCase() === 'en' ? 'en' : 'fr';
        var guestSingular = searchCard.getAttribute('data-guests-singular') || (language === 'en' ? 'guest' : 'invité');
        var guestPlural = searchCard.getAttribute('data-guests-plural') || (language === 'en' ? 'guests' : 'invités');
        var overlay = document.createElement('div');
        overlay.className = 'loft-datepicker-overlay';
        document.body.appendChild(overlay);

        var MIN_GUESTS = 1;
        var MAX_GUESTS = 10;
        var ONE_DAY = 86400000;

        function parseDate(value) {
            if (typeof value !== 'string' || !value) {
                return null;
            }

            var parts = value.split('/');
            if (parts.length !== 3) {
                return null;
            }

            var month = parseInt(parts[0], 10) - 1;
            var day = parseInt(parts[1], 10);
            var year = parseInt(parts[2], 10);

            if (parts[2].length === 2) {
                year += year < 70 ? 2000 : 1900;
            }

            if (isNaN(month) || isNaN(day) || isNaN(year)) {
                return null;
            }

            var date = new Date(year, month, day);
            return isNaN(date.getTime()) ? null : date;
        }

        function formatDateForInput(date) {
            if (!(date instanceof Date)) {
                return '';
            }

            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            var year = date.getFullYear();
            return month + '/' + day + '/' + year;
        }

        function clampGuests(value) {
            var parsed = parseInt(value, 10);
            if (isNaN(parsed) || parsed < MIN_GUESTS) {
                parsed = MIN_GUESTS;
            }
            if (parsed > MAX_GUESTS) {
                parsed = MAX_GUESTS;
            }
            return parsed;
        }

        function updateGuestsDisplay() {
            if (!guestInput || !guestValue) {
                return;
            }
            var value = clampGuests(guestInput.value);
            guestInput.value = value;
            guestValue.textContent = value + ' ' + (value === 1 ? guestSingular : guestPlural);
            if (totalGuestsInput) {
                totalGuestsInput.value = value;
            }
        }

        function adjustGuests(direction) {
            if (!guestInput) {
                return;
            }
            var current = clampGuests(guestInput.value);
            current = direction === 'up' ? current + 1 : current - 1;
            guestInput.value = clampGuests(current);
            updateGuestsDisplay();
        }

        if (guestGroup) {
            var buttons = guestGroup.querySelectorAll('.loft-search-toolbar__guest-btn');
            Array.prototype.forEach.call(buttons, function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    var dir = button.getAttribute('data-direction') === 'down' ? 'down' : 'up';
                    adjustGuests(dir);
                });
            });
            updateGuestsDisplay();
        }

        function formatDisplayRange(start, end) {
            var locale = language === 'en' ? 'en-CA' : 'fr-CA';
            var formatter = new Intl.DateTimeFormat(locale, {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
            return formatter.format(start) + ' – ' + formatter.format(end);
        }

        function updateDateFields(dates) {
            if (!Array.isArray(dates)) {
                dates = [];
            }

            var start = dates[0] ? new Date(dates[0]) : null;
            var end = dates[1] ? new Date(dates[1]) : null;

            if (start && end && end < start) {
                var temp = start;
                start = end;
                end = temp;
            }

            if (!start && checkInInput && checkInInput.value) {
                start = parseDate(checkInInput.value);
            }

            if (!end && checkOutInput && checkOutInput.value) {
                end = parseDate(checkOutInput.value);
            }

            if (start && end) {
                if (checkInInput) {
                    checkInInput.value = formatDateForInput(start);
                }
                if (checkOutInput) {
                    checkOutInput.value = formatDateForInput(end);
                }
                if (dateInput) {
                    dateInput.value = formatDisplayRange(start, end);
                    dateInput.setAttribute('data-has-value', 'true');
                }
            } else {
                if (checkInInput) {
                    checkInInput.value = '';
                }
                if (checkOutInput) {
                    checkOutInput.value = '';
                }
                if (dateInput) {
                    dateInput.value = '';
                    dateInput.setAttribute('data-has-value', 'false');
                }
            }
        }

        var fpInstance = null;
        if (window.flatpickr && dateInput) {
            var mobileQuery = window.matchMedia('(max-width: 480px)');

            function isMobileViewport() {
                return mobileQuery && mobileQuery.matches;
            }

            function setOverlayVisibility(isVisible) {
                if (isVisible) {
                    document.body.classList.add('loft-datepicker-open');
                    overlay.classList.add('is-visible');
                } else {
                    document.body.classList.remove('loft-datepicker-open');
                    overlay.classList.remove('is-visible');
                }
            }

            function applyCalendarLayout(instance) {
                var calendar = instance && instance.calendarContainer;
                if (!calendar) {
                    return;
                }

                if (isMobileViewport()) {
                    calendar.classList.add('flatpickr-bottom-sheet');
                    setOverlayVisibility(true);
                } else {
                    calendar.classList.remove('flatpickr-bottom-sheet');
                    setOverlayVisibility(false);
                }
            }

            if (mobileQuery) {
                var handleMobileChange = function () {
                    if (!fpInstance) {
                        return;
                    }
                    var shouldShowOverlay = fpInstance.isOpen && isMobileViewport();
                    applyCalendarLayout(fpInstance);
                    if (!shouldShowOverlay) {
                        setOverlayVisibility(false);
                    }
                };
                if (mobileQuery.addEventListener) {
                    mobileQuery.addEventListener('change', handleMobileChange);
                } else if (mobileQuery.addListener) {
                    mobileQuery.addListener(handleMobileChange);
                }
            }

            fpInstance = window.flatpickr(dateInput, {
                mode: 'range',
                dateFormat: 'm/d/Y',
                minDate: 'today',
                defaultDate: [],
                monthSelectorType: 'static',
                clickOpens: true,
                inline: false,
                static: false,
                locale: language === 'en' ? 'default' : 'fr',
                onOpen: function (selectedDates, dateStr, instance) {
                    applyCalendarLayout(instance);
                },
                onClose: function () {
                    setOverlayVisibility(false);
                },
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2 && selectedDates[1] < selectedDates[0]) {
                        var swapped = [selectedDates[1], selectedDates[0]];
                        instance.setDate(swapped, false);
                        selectedDates = swapped;
                    }
                    updateDateFields(selectedDates);
                    if (!isMobileViewport() && selectedDates.length === 2) {
                        instance.close();
                    }
                }
            });
        }

        if (dateClear && fpInstance) {
            dateClear.addEventListener('click', function (event) {
                event.preventDefault();
                fpInstance.clear();
                updateDateFields([]);
            });
        }

        overlay.addEventListener('click', function () {
            if (fpInstance) {
                fpInstance.close();
            }
        });

        var initialDates = [];
        var initialStart = parseDate(checkInInput ? checkInInput.value : '');
        var initialEnd = parseDate(checkOutInput ? checkOutInput.value : '');
        if (initialStart) {
            initialDates.push(initialStart);
        }
        if (initialEnd) {
            initialDates.push(initialEnd);
        }
        if (fpInstance && initialDates.length) {
            fpInstance.setDate(initialDates, false);
        }
        updateDateFields(initialDates);

        if (promoToggle && promoField) {
            promoToggle.addEventListener('click', function (event) {
                event.preventDefault();
                var isHidden = promoField.hasAttribute('hidden');
                if (isHidden) {
                    promoField.removeAttribute('hidden');
                } else {
                    promoField.setAttribute('hidden', 'hidden');
                }
            });
        }

        if (promoInput && promoCheckoutInput) {
            promoInput.addEventListener('input', function () {
                promoCheckoutInput.value = promoInput.value;
            });
        }

        function handleSubmit(event) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            updateTotalGuests();
            form.submit();
        }

        if (submitButton) {
            submitButton.addEventListener('click', handleSubmit);
        }

        form.addEventListener('submit', handleSubmit);
    }

    ready(function () {
        var body = document.body;
        if (!body || !body.classList.contains('loft1325-mobile-home-active')) {
            return;
        }

        initializeNav(body);
        initializeSearchForm();
    });
})();
