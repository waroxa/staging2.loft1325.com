<?php
/**
 * Mobile-only front page template.
 *
 * @package Loft1325\MobileHomepage
 */

defined( 'ABSPATH' ) || exit;

?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lofts 1325 · Mobile Booking</title>
  <?php wp_head(); ?>
  <style>
    :root {
      color-scheme: light;
      --black: #0b0b0b;
      --white: #ffffff;
      --gray-100: #f5f5f5;
      --gray-200: #e5e5e5;
      --gray-300: #d7d7d7;
      --gray-500: #7a7a7a;
      --shadow: 0 18px 32px rgba(0, 0, 0, 0.08);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Inter", "Helvetica Neue", sans-serif;
      background: var(--white);
      color: var(--black);
    }

    .mobile-shell {
      max-width: 430px;
      margin: 0 auto;
      min-height: 100vh;
      background: var(--white);
      display: flex;
      flex-direction: column;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 10;
      background: var(--white);
      border-bottom: 1px solid var(--gray-200);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
    }

    .logo {
      height: 26px;
      width: auto;
    }

    .icon-button {
      border: 1px solid var(--black);
      background: transparent;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      line-height: 1;
      color: var(--black);
    }

    .hero {
      padding: 20px;
      background: var(--gray-100);
      border-bottom: 1px solid var(--gray-200);
    }

    .hero h1 {
      font-family: "Playfair Display", serif;
      font-size: 26px;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 14px;
      color: var(--gray-500);
    }

    .search-panel {
      margin-top: 18px;
      display: grid;
      gap: 12px;
    }

    .search-tile {
      border: 1px solid var(--black);
      padding: 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      background: var(--white);
    }

    .search-tile span {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
    }

    .search-tile strong {
      font-size: 15px;
      letter-spacing: 0.03em;
    }

    .filters {
      display: flex;
      gap: 12px;
      align-items: center;
      padding: 16px 20px;
      border-bottom: 1px solid var(--gray-200);
    }

    .filters label {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
    }

    .filters input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: var(--black);
    }

    .room-list {
      padding: 20px;
      display: grid;
      gap: 20px;
    }

    .room-card {
      border: 1px solid var(--gray-200);
      background: var(--white);
      box-shadow: var(--shadow);
    }

    .room-card img {
      width: 100%;
      height: 210px;
      object-fit: cover;
      display: block;
    }

    .room-body {
      padding: 16px;
      display: grid;
      gap: 12px;
    }

    .room-title {
      font-size: 20px;
      font-weight: 600;
    }

    .room-meta {
      font-size: 13px;
      color: var(--gray-500);
    }

    .room-features {
      font-size: 13px;
      line-height: 1.6;
    }

    .rate-block {
      border-top: 1px solid var(--gray-200);
      padding-top: 12px;
      display: grid;
      gap: 10px;
    }

    .rate-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      font-size: 14px;
    }

    .rate-row strong {
      font-size: 18px;
    }

    .primary-button {
      width: 100%;
      padding: 12px 14px;
      background: var(--black);
      color: var(--white);
      border: 1px solid var(--black);
      text-transform: uppercase;
      letter-spacing: 0.2em;
      font-size: 12px;
    }

    .sticky-bar {
      position: sticky;
      bottom: 0;
      z-index: 8;
      border-top: 1px solid var(--gray-200);
      background: var(--white);
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
    }

    .sticky-price {
      font-size: 16px;
      font-weight: 600;
    }

    .sticky-note {
      font-size: 12px;
      color: var(--gray-500);
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 20;
    }

    .modal.active {
      display: flex;
    }

    .dates-modal .modal-content {
      width: 100%;
      max-width: 430px;
      height: 100%;
      background: var(--white);
      display: flex;
      flex-direction: column;
      gap: 28px;
      padding: 18px 18px 120px;
      overflow-y: auto;
    }

    .dates-modal__header {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      padding-top: 6px;
    }

    .dates-modal__header h2 {
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.2em;
    }

    .dates-modal__header .icon-button {
      position: absolute;
      right: 0;
      top: 0;
      border: none;
      font-size: 22px;
    }

    .dates-modal__section {
      display: grid;
      gap: 16px;
    }

    .dates-modal__section-title {
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .dates-modal__month {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 16px;
      font-weight: 600;
      letter-spacing: 0.08em;
    }

    .dates-modal__chevron {
      border: none;
      background: transparent;
      font-size: 28px;
      line-height: 1;
      color: var(--black);
    }

    .calendar {
      display: grid;
      gap: 12px;
    }

    .calendar-weekdays,
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
    }

    .calendar-weekdays span {
      font-size: 12px;
      letter-spacing: 0.1em;
      color: var(--gray-500);
    }

    .calendar-day {
      border: 1px solid transparent;
      background: transparent;
      min-height: 56px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
      position: relative;
      font-size: 14px;
      color: var(--black);
    }

    .calendar-day.is-empty {
      border: none;
      background: transparent;
    }

    .calendar-day .day-price {
      font-size: 11px;
      color: var(--gray-500);
    }

    .calendar-day.is-range {
      background: var(--black);
      color: var(--white);
    }

    .calendar-day.is-range .day-price {
      color: var(--white);
    }

    .calendar-day.is-start,
    .calendar-day.is-end {
      border: 2px solid var(--black);
      background: var(--white);
      color: var(--black);
      z-index: 1;
    }

    .calendar-day.is-start.is-range,
    .calendar-day.is-end.is-range {
      background: var(--white);
      color: var(--black);
    }

    .calendar-day.is-disabled {
      color: var(--gray-500);
      pointer-events: none;
    }

    .calendar-day.is-disabled .day-price {
      color: var(--gray-300);
    }

    .calendar-day.no-checkin::before,
    .calendar-day.no-checkout::after {
      content: "";
      position: absolute;
      width: 12px;
      height: 12px;
      border-top: 2px solid var(--gray-300);
      border-right: 2px solid var(--gray-300);
      top: 4px;
      right: 6px;
      transform: rotate(135deg);
    }

    .calendar-day.no-checkout::after {
      top: auto;
      bottom: 4px;
      right: 6px;
      transform: rotate(-45deg);
    }

    .calendar-day .soldout-mark {
      position: absolute;
      font-size: 24px;
      color: var(--gray-500);
      opacity: 0.7;
    }

    .calendar-legend {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      font-size: 12px;
    }

    .legend-item {
      border: 1px solid var(--gray-300);
      padding: 8px 10px;
      text-align: center;
      position: relative;
      background: linear-gradient(135deg, transparent 45%, rgba(0, 0, 0, 0.05) 45%, rgba(0, 0, 0, 0.05) 55%, transparent 55%);
    }

    .price-summary {
      display: grid;
      gap: 4px;
    }

    .price-line {
      font-size: 15px;
      font-weight: 600;
    }

    .price-sub {
      font-size: 12px;
      color: var(--gray-500);
    }

    .guests-section {
      gap: 12px;
    }

    .guest-card {
      border: 1px solid var(--gray-300);
      padding: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .guest-title {
      font-size: 14px;
      font-weight: 600;
    }

    .guest-sub {
      font-size: 12px;
      color: var(--gray-500);
    }

    .counter {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .counter button {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--black);
      background: transparent;
      font-size: 18px;
    }

    .counter span {
      min-width: 20px;
      text-align: center;
      font-weight: 600;
    }

    .dates-modal__sticky {
      position: sticky;
      bottom: 0;
      background: var(--white);
      border-top: 1px solid var(--gray-200);
      padding: 12px 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .dates-modal__caret {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: 1px solid var(--black);
      background: var(--black);
      color: var(--white);
      font-size: 16px;
      line-height: 1;
    }

    @media (min-width: 768px) {
      body {
        display: flex;
        justify-content: center;
        background: var(--gray-100);
        padding: 40px 0;
      }

      .mobile-shell {
        border: 1px solid var(--gray-300);
        box-shadow: var(--shadow);
      }
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
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

  <?php wp_footer(); ?>
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
