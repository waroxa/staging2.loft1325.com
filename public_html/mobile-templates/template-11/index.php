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
