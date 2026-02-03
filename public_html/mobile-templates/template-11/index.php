<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lofts 1325 · Mobile Booking Template</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-11.css" />
</head>
<body>
  <main class="mobile-shell">
    <header class="header">
      <div class="header-inner">
        <button class="icon-button" type="button" aria-label="Ouvrir le menu">≡</button>
        <img
          class="logo"
          src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
          srcset="https://loft1325.com/wp-content/uploads/2024/06/Asset-1-300x108.png 300w, https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png 518w"
          sizes="(max-width: 430px) 180px, 220px"
          alt="Lofts 1325"
        />
        <button class="icon-button" type="button" aria-label="Accéder au profil">⋯</button>
      </div>
    </header>

    <section class="hero">
      <h1>Sélectionner une chambre</h1>
      <p>Mobile-only, noir &amp; blanc, avec réservation intégrée.</p>
      <div class="search-panel">
        <button class="search-tile" id="openSearch" type="button">
          <span>Dates</span>
          <strong id="dateSummary">19 févr. · 21 févr.</strong>
        </button>
        <button class="search-tile" id="openGuests" type="button">
          <span>Voyageurs</span>
          <strong id="guestSummary">2 adultes · 0 enfant</strong>
        </button>
      </div>
    </section>

    <section class="filters">
      <label>
        <input type="checkbox" checked />
        Utiliser les points
      </label>
      <button class="icon-button" type="button" aria-label="Filtrer">⚙</button>
    </section>

    <section class="room-list">
      <article class="room-card">
        <img
          src="/wp-content/uploads/2022/04/room01.jpg"
          alt="Suite signature"
        />
        <div class="room-body">
          <div>
            <p class="room-title">Suite Signature</p>
            <p class="room-meta">À partir de 340 $CA · par nuit</p>
          </div>
          <p class="room-features">
            Lit King · 420 pieds carrés · Salle de bain marbre · Salon privé
          </p>
          <div class="rate-block">
            <div class="rate-row">
              <span>Tarif membre Loft Circle</span>
              <strong>340 $CA</strong>
            </div>
            <button class="primary-button" type="button">Réserver maintenant</button>
          </div>
        </div>
      </article>

      <article class="room-card">
        <img
          src="/wp-content/uploads/2022/04/room05.jpg"
          alt="Suite penthouse"
        />
        <div class="room-body">
          <div>
            <p class="room-title">Suite Penthouse</p>
            <p class="room-meta">À partir de 454 $CA · par nuit</p>
          </div>
          <p class="room-features">
            Terrasse privée · Vue sur le fleuve · Service majordome · 2 salles d'eau
          </p>
          <div class="rate-block">
            <div class="rate-row">
              <span>Tarif flexible</span>
              <strong>454 $CA</strong>
            </div>
            <button class="primary-button" type="button">Réserver maintenant</button>
          </div>
        </div>
      </article>

      <article class="room-card">
        <img
          src="/wp-content/uploads/2022/04/room06.jpg"
          alt="Loft atelier"
        />
        <div class="room-body">
          <div>
            <p class="room-title">Loft Atelier</p>
            <p class="room-meta">À partir de 523 $CA · par nuit</p>
          </div>
          <p class="room-features">
            Plafonds 14 pieds · Bar discret · Accès galerie · Accueil privé
          </p>
          <div class="rate-block">
            <div class="rate-row">
              <span>Forfait coeur à coeur</span>
              <strong>523 $CA</strong>
            </div>
            <button class="primary-button" type="button">Réserver maintenant</button>
          </div>
        </div>
      </article>
    </section>

    <section class="sticky-bar">
      <div>
        <p class="sticky-price">340,00 $CA</p>
        <p class="sticky-note">Vous avez trouvé le meilleur prix.</p>
      </div>
      <button class="primary-button" type="button">Finaliser</button>
    </section>
  </main>

  <div class="modal dates-modal" id="searchModal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="searchTitle">
      <div class="dates-modal__header">
        <h2 id="searchTitle">SEARCH</h2>
        <button class="icon-button" type="button" id="closeModal" aria-label="Fermer">×</button>
      </div>

      <section class="dates-modal__section">
        <div class="dates-modal__section-title">DATES</div>
        <div class="dates-modal__month">
          <span id="calendarMonthLabel">FEBRUARY 2026</span>
          <button class="dates-modal__chevron" type="button" id="nextMonth" aria-label="Mois suivant">›</button>
        </div>

        <div class="calendar">
          <div class="calendar-weekdays">
            <span>S</span>
            <span>M</span>
            <span>T</span>
            <span>W</span>
            <span>T</span>
            <span>F</span>
            <span>S</span>
          </div>
          <div class="calendar-grid" id="calendarGrid"></div>
        </div>

        <div class="calendar-legend">
          <span class="legend-item legend-checkin">No Check-in</span>
          <span class="legend-item legend-checkout">No Check-out</span>
        </div>

        <div class="price-summary">
          <p class="price-line" id="priceSummary">From CA$237 total for 1 night</p>
          <p class="price-sub">Excluding taxes and fees</p>
        </div>
      </section>

      <section class="dates-modal__section guests-section">
        <div class="dates-modal__section-title">GUESTS</div>
        <div class="guest-card">
          <div>
            <p class="guest-title">Adults (Ages 18 or above)</p>
            <p class="guest-sub"> </p>
          </div>
          <div class="counter" data-target="adultCount">
            <button type="button" class="minus">-</button>
            <span id="adultCount">2</span>
            <button type="button" class="plus">+</button>
          </div>
        </div>
        <div class="guest-card">
          <div>
            <p class="guest-title">Children (Ages 0-17)</p>
            <p class="guest-sub"> </p>
          </div>
          <div class="counter" data-target="childCount">
            <button type="button" class="minus">-</button>
            <span id="childCount">0</span>
            <button type="button" class="plus">+</button>
          </div>
        </div>
      </section>

      <div class="dates-modal__sticky">
        <div>
          <p class="sticky-price" id="modalStickyPrice">CA$237.00</p>
          <p class="sticky-note">You have found the best price!</p>
        </div>
        <button class="dates-modal__caret" type="button" aria-label="Collapse">⌃</button>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById('searchModal');
    const openSearch = document.getElementById('openSearch');
    const openGuests = document.getElementById('openGuests');
    const closeModal = document.getElementById('closeModal');
    const dateSummary = document.getElementById('dateSummary');
    const guestSummary = document.getElementById('guestSummary');
    const calendarGrid = document.getElementById('calendarGrid');
    const calendarMonthLabel = document.getElementById('calendarMonthLabel');
    const nextMonthButton = document.getElementById('nextMonth');
    const priceSummary = document.getElementById('priceSummary');
    const modalStickyPrice = document.getElementById('modalStickyPrice');

    const adultCount = document.getElementById('adultCount');
    const childCount = document.getElementById('childCount');

    const TOTAL_UNITS = 22;
    const state = {
      selectedStart: null,
      selectedEnd: null,
      currentMonth: startOfMonth(new Date()),
      ratesCache: new Map(),
      occupancyCache: new Map(),
      restrictionsCache: new Map()
    };

    function formatDate(dateValue) {
      if (!dateValue) return '';
      return dateValue.toLocaleDateString('fr-CA', {
        month: 'short',
        day: '2-digit'
      });
    }

    function formatCurrency(value) {
      return `CA$${value.toFixed(0)}`;
    }

    function toISODate(date) {
      return date.toISOString().split('T')[0];
    }

    function startOfMonth(date) {
      return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    function endOfMonth(date) {
      return new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }

    function addMonths(date, amount) {
      return new Date(date.getFullYear(), date.getMonth() + amount, 1);
    }

    function isSameDay(a, b) {
      return a && b && a.toDateString() === b.toDateString();
    }

    function isBetween(date, start, end) {
      return start && end && date > start && date < end;
    }

    function daysBetween(start, end) {
      const days = [];
      const current = new Date(start);
      while (current <= end) {
        days.push(new Date(current));
        current.setDate(current.getDate() + 1);
      }
      return days;
    }

    function updateSummary() {
      const arrival = state.selectedStart ? formatDate(state.selectedStart) : '19 févr.';
      const depart = state.selectedEnd ? formatDate(state.selectedEnd) : '21 févr.';
      dateSummary.textContent = `${arrival} · ${depart}`;
      guestSummary.textContent = `${adultCount.textContent} adultes · ${childCount.textContent} enfant`;
    }

    function openModal() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      preloadMonths();
      renderCalendar();
    }

    function closeModalView() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
    }

    function getMonthLabel(date) {
      return date.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
      }).toUpperCase();
    }

    function getMonthKey(start, end, guests) {
      return `${toISODate(start)}_${toISODate(end)}_${guests}`;
    }

    function mockRatesForRange(start, end) {
      const data = {};
      const cursor = new Date(start);
      while (cursor <= end) {
        const base = 220 + (cursor.getMonth() + 1) * 7;
        const price = base + (cursor.getDate() % 8) * 18;
        data[toISODate(cursor)] = price;
        cursor.setDate(cursor.getDate() + 1);
      }
      return data;
    }

    function mockOccupancyForRange(start, end) {
      const data = {};
      const cursor = new Date(start);
      while (cursor <= end) {
        const day = cursor.getDate();
        const monthFactor = (cursor.getMonth() + 3) % 6;
        const occupancy = Math.min(TOTAL_UNITS, 10 + (day % 9) + monthFactor);
        data[toISODate(cursor)] = occupancy;
        cursor.setDate(cursor.getDate() + 1);
      }
      return data;
    }

    function mockRestrictionsForRange(start, end) {
      const data = {};
      const cursor = new Date(start);
      while (cursor <= end) {
        const iso = toISODate(cursor);
        data[iso] = {
          noCheckIn: cursor.getDay() === 2,
          noCheckOut: cursor.getDay() === 5
        };
        cursor.setDate(cursor.getDate() + 1);
      }
      return data;
    }

    async function getDailyRates(monthStart, monthEnd, guestCount, promoCode) {
      const key = getMonthKey(monthStart, monthEnd, guestCount);
      if (state.ratesCache.has(key)) {
        return state.ratesCache.get(key);
      }

      // TODO: Replace with real rates endpoint.
      // Example: const response = await fetch(`/api/rates?start=${toISODate(monthStart)}&end=${toISODate(monthEnd)}&guests=${guestCount}&promo=${promoCode || ''}`);
      // const data = await response.json();
      const data = mockRatesForRange(monthStart, monthEnd);
      state.ratesCache.set(key, data);
      return data;
    }

    async function getOccupancyByDateRange(startDate, endDate) {
      const key = `${toISODate(startDate)}_${toISODate(endDate)}`;
      if (state.occupancyCache.has(key)) {
        return state.occupancyCache.get(key);
      }

      // TODO: Replace with Butterfly adapter call.
      // Example: const response = await fetch(`/api/butterfly/occupancy?start=${toISODate(startDate)}&end=${toISODate(endDate)}`);
      // const data = await response.json();
      const data = mockOccupancyForRange(startDate, endDate);
      state.occupancyCache.set(key, data);
      return data;
    }

    async function getRestrictionsByDateRange(startDate, endDate) {
      const key = `${toISODate(startDate)}_${toISODate(endDate)}`;
      if (state.restrictionsCache.has(key)) {
        return state.restrictionsCache.get(key);
      }

      // TODO: Replace with restrictions endpoint.
      const data = mockRestrictionsForRange(startDate, endDate);
      state.restrictionsCache.set(key, data);
      return data;
    }

    async function preloadMonths() {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const nextStart = addMonths(monthStart, 1);
      const nextEnd = endOfMonth(nextStart);
      const guests = Number(adultCount.textContent) + Number(childCount.textContent);

      await Promise.all([
        getDailyRates(monthStart, monthEnd, guests),
        getDailyRates(nextStart, nextEnd, guests),
        getOccupancyByDateRange(monthStart, nextEnd),
        getRestrictionsByDateRange(monthStart, nextEnd)
      ]);
    }

    async function renderCalendar() {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const guests = Number(adultCount.textContent) + Number(childCount.textContent);

      const [rates, occupancy, restrictions] = await Promise.all([
        getDailyRates(monthStart, monthEnd, guests),
        getOccupancyByDateRange(monthStart, monthEnd),
        getRestrictionsByDateRange(monthStart, monthEnd)
      ]);

      calendarMonthLabel.textContent = getMonthLabel(monthStart);
      calendarGrid.innerHTML = '';

      const firstDay = monthStart.getDay();
      for (let i = 0; i < firstDay; i += 1) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-day is-empty';
        calendarGrid.appendChild(emptyCell);
      }

      const today = new Date();
      const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());

      for (let day = 1; day <= monthEnd.getDate(); day += 1) {
        const date = new Date(monthStart.getFullYear(), monthStart.getMonth(), day);
        const iso = toISODate(date);
        const price = rates[iso];
        const occupiedUnits = occupancy[iso] ?? 0;
        const restriction = restrictions[iso] || { noCheckIn: false, noCheckOut: false };
        const soldOut = occupiedUnits >= TOTAL_UNITS;
        const isPast = date < todayMidnight;
        const isDisabled = soldOut || isPast;

        const cell = document.createElement('button');
        cell.type = 'button';
        cell.className = 'calendar-day';
        cell.dataset.date = iso;

        if (restriction.noCheckIn) {
          cell.classList.add('no-checkin');
        }
        if (restriction.noCheckOut) {
          cell.classList.add('no-checkout');
        }
        if (soldOut) {
          cell.classList.add('is-soldout');
        }
        if (isDisabled) {
          cell.classList.add('is-disabled');
        }
        if (isSameDay(date, state.selectedStart)) {
          cell.classList.add('is-start');
        }
        if (isSameDay(date, state.selectedEnd)) {
          cell.classList.add('is-end');
        }
        if (isBetween(date, state.selectedStart, state.selectedEnd)) {
          cell.classList.add('is-range');
        }

        const dayNumber = document.createElement('span');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;

        const dayPrice = document.createElement('span');
        dayPrice.className = 'day-price';
        if (!soldOut && price) {
          dayPrice.textContent = price.toFixed(0);
        } else {
          dayPrice.textContent = '';
        }

        const soldOutMark = document.createElement('span');
        soldOutMark.className = 'soldout-mark';
        if (soldOut && date >= todayMidnight) {
          soldOutMark.textContent = '×';
        }

        cell.append(dayNumber, dayPrice, soldOutMark);

        if (!isDisabled) {
          cell.addEventListener('click', () => handleDateClick(date, restriction));
        }

        calendarGrid.appendChild(cell);
      }

      updatePriceSummary(rates);
    }

    function updatePriceSummary(rates) {
      let price = null;
      if (state.selectedStart) {
        price = rates[toISODate(state.selectedStart)];
      }
      if (!price) {
        const values = Object.values(rates).filter(Boolean);
        price = values.length ? Math.min(...values) : 0;
      }
      const formatted = formatCurrency(price || 0);
      priceSummary.textContent = `From ${formatted} total for 1 night`;
      modalStickyPrice.textContent = `${formatted}.00`;
    }

    function isRangeAvailable(start, end, occupancy, restrictions) {
      const dates = daysBetween(start, end);
      return dates.every((date, index) => {
        const iso = toISODate(date);
        const occupiedUnits = occupancy[iso] ?? 0;
        const restriction = restrictions[iso] || { noCheckIn: false, noCheckOut: false };
        if (occupiedUnits >= TOTAL_UNITS) return false;
        if (index === 0 && restriction.noCheckIn) return false;
        if (index === dates.length - 1 && restriction.noCheckOut) return false;
        return true;
      });
    }

    async function handleDateClick(date, restriction) {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const occupancy = await getOccupancyByDateRange(monthStart, monthEnd);
      const restrictions = await getRestrictionsByDateRange(monthStart, monthEnd);

      if (!state.selectedStart || (state.selectedStart && state.selectedEnd)) {
        if (restriction.noCheckIn) return;
        state.selectedStart = date;
        state.selectedEnd = null;
      } else if (state.selectedStart && !state.selectedEnd) {
        if (date <= state.selectedStart) {
          if (restriction.noCheckIn) return;
          state.selectedStart = date;
          state.selectedEnd = null;
        } else {
          const rangeOk = isRangeAvailable(state.selectedStart, date, occupancy, restrictions);
          if (!rangeOk) {
            state.selectedStart = date;
            state.selectedEnd = null;
          } else {
            const endRestriction = restrictions[toISODate(date)] || { noCheckOut: false };
            if (endRestriction.noCheckOut) return;
            state.selectedEnd = date;
          }
        }
      }
      updateSummary();
      renderCalendar();
    }

    openSearch.addEventListener('click', openModal);
    openGuests.addEventListener('click', openModal);
    closeModal.addEventListener('click', closeModalView);

    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModalView();
      }
    });

    nextMonthButton.addEventListener('click', () => {
      state.currentMonth = addMonths(state.currentMonth, 1);
      preloadMonths();
      renderCalendar();
    });

    document.querySelectorAll('.counter').forEach((counter) => {
      const minus = counter.querySelector('.minus');
      const plus = counter.querySelector('.plus');
      const target = document.getElementById(counter.dataset.target);

      minus.addEventListener('click', () => {
        const value = Math.max(0, Number(target.textContent) - 1);
        target.textContent = value;
        preloadMonths();
        renderCalendar();
        updateSummary();
      });

      plus.addEventListener('click', () => {
        target.textContent = Number(target.textContent) + 1;
        preloadMonths();
        renderCalendar();
        updateSummary();
      });
    });

    updateSummary();
  </script>
</body>
</html>
