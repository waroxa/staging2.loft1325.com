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
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      z-index: 20;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      width: 100%;
      max-width: 420px;
      background: var(--white);
      border: 1px solid var(--black);
      padding: 20px;
      display: grid;
      gap: 16px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      font-size: 18px;
      text-transform: uppercase;
      letter-spacing: 0.12em;
    }

    .date-grid {
      display: grid;
      gap: 12px;
    }

    .date-grid label {
      display: grid;
      gap: 6px;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.16em;
    }

    .date-grid input {
      border: 1px solid var(--gray-300);
      padding: 10px;
      font-size: 15px;
    }

    .guest-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 1px solid var(--gray-300);
      padding: 12px;
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

  <div class="modal" id="searchModal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="searchTitle">
      <div class="modal-header">
        <h2 id="searchTitle">Rechercher</h2>
        <button class="icon-button" type="button" id="closeModal" aria-label="Fermer">×</button>
      </div>

      <div class="date-grid">
        <label>
          Arrivée
          <input type="date" id="checkIn" />
        </label>
        <label>
          Départ
          <input type="date" id="checkOut" />
        </label>
      </div>

      <div class="guest-row">
        <div>
          <p>Adultes</p>
          <p class="room-meta">18 ans ou plus</p>
        </div>
        <div class="counter" data-target="adultCount">
          <button type="button" class="minus">-</button>
          <span id="adultCount">2</span>
          <button type="button" class="plus">+</button>
        </div>
      </div>

      <div class="guest-row">
        <div>
          <p>Enfants</p>
          <p class="room-meta">0-17 ans</p>
        </div>
        <div class="counter" data-target="childCount">
          <button type="button" class="minus">-</button>
          <span id="childCount">0</span>
          <button type="button" class="plus">+</button>
        </div>
      </div>

      <button class="primary-button" type="button" id="applySearch">Rechercher</button>
    </div>
  </div>

  <?php wp_footer(); ?>
  <script>
    const modal = document.getElementById('searchModal');
    const openSearch = document.getElementById('openSearch');
    const openGuests = document.getElementById('openGuests');
    const closeModal = document.getElementById('closeModal');
    const applySearch = document.getElementById('applySearch');
    const dateSummary = document.getElementById('dateSummary');
    const guestSummary = document.getElementById('guestSummary');

    const checkIn = document.getElementById('checkIn');
    const checkOut = document.getElementById('checkOut');
    const adultCount = document.getElementById('adultCount');
    const childCount = document.getElementById('childCount');

    function formatDate(dateValue) {
      if (!dateValue) return '';
      const date = new Date(dateValue);
      return date.toLocaleDateString('fr-CA', {
        month: 'short',
        day: '2-digit'
      });
    }

    function updateSummary() {
      const arrival = formatDate(checkIn.value) || '19 févr.';
      const depart = formatDate(checkOut.value) || '21 févr.';
      dateSummary.textContent = `${arrival} · ${depart}`;
      guestSummary.textContent = `${adultCount.textContent} adultes · ${childCount.textContent} enfant`;
    }

    function openModal() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModalView() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
    }

    openSearch.addEventListener('click', openModal);
    openGuests.addEventListener('click', openModal);
    closeModal.addEventListener('click', closeModalView);

    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModalView();
      }
    });

    document.querySelectorAll('.counter').forEach((counter) => {
      const minus = counter.querySelector('.minus');
      const plus = counter.querySelector('.plus');
      const target = document.getElementById(counter.dataset.target);

      minus.addEventListener('click', () => {
        const value = Math.max(0, Number(target.textContent) - 1);
        target.textContent = value;
      });

      plus.addEventListener('click', () => {
        target.textContent = Number(target.textContent) + 1;
      });
    });

    applySearch.addEventListener('click', () => {
      updateSummary();
      closeModalView();
    });

    updateSummary();
  </script>
</body>
</html>
