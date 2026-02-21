<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lofts 1325 · Template 12 Single Room</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-11.css" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-12.css" />
</head>
<body>
  <main class="mobile-shell">
    <header class="header template-12-home-header">
      <div class="header-inner template-12-home-header__inner">
        <button class="template-12-home-header__menu" type="button" aria-label="Ouvrir le menu">☰</button>
        <img
          class="logo"
          src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
          srcset="https://loft1325.com/wp-content/uploads/2024/06/Asset-1-300x108.png 300w, https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png 518w"
          sizes="(max-width: 430px) 180px, 220px"
          alt="Lofts 1325"
        />
        <button class="template-12-home-header__lang" type="button" aria-label="Changer la langue">
          <span class="is-active">FR</span>
          <span>·</span>
          <span>EN</span>
        </button>
      </div>
    </header>

    <section class="hero template-12-hero" data-nd-room>
      <h1 data-nd-title>PENTHOUSE</h1>
      <p data-nd-price-line>À partir de 400 CAD · par nuit</p>
    </section>

    <section class="room-list template-12-list">
      <article class="room-card">
        <div class="template-12-slider" data-template12-slider>
          <div class="template-12-slider__track" data-template12-slider-track>
            <figure class="template-12-slider__slide"><img src="/wp-content/uploads/2022/04/room05.jpg" alt="Penthouse · Salon" data-nd-image /></figure>
            <figure class="template-12-slider__slide"><img src="/wp-content/uploads/2022/04/room06.jpg" alt="Penthouse · Chambre" /></figure>
            <figure class="template-12-slider__slide"><img src="/wp-content/uploads/2022/04/room01.jpg" alt="Penthouse · Cuisine" /></figure>
          </div>
          <span class="template-12-slider__badge">Expérience 5 étoiles</span>
          <div class="template-12-slider__nav">
            <button type="button" class="template-12-slider__btn" data-template12-prev aria-label="Image précédente">‹</button>
            <button type="button" class="template-12-slider__btn" data-template12-next aria-label="Image suivante">›</button>
          </div>
          <div class="template-12-slider__dots" data-template12-dots></div>
        </div>

        <div class="room-body">
          <p class="room-features" data-nd-description>
            Profitez d'un séjour luxueux dans notre penthouse exclusif. Cet espace spacieux et lumineux offre une cuisine gastronomique entièrement équipée avec un îlot en marbre, des appareils haut de gamme, une salle de bain somptueuse et une vue imprenable sur la ville. Parfait pour les escapades romantiques ou les séjours prolongés.
          </p>

          <ul class="template-12-facts" data-nd-facts>
            <li>
              <p class="template-12-fact__label">Capacité</p>
              <strong>2 personnes</strong>
              <small>Lit queen premium & ambiance calme</small>
            </li>
            <li>
              <p class="template-12-fact__label">Surface</p>
              <strong>82 m²</strong>
              <small>Espace loft ouvert et lumineux</small>
            </li>
            <li>
              <p class="template-12-fact__label">Nuits minimales</p>
              <strong>1</strong>
              <small>Check-in autonome 24/7</small>
            </li>
          </ul>

          <div class="template-12-amenities" aria-label="Détails du séjour">
            <span>Wi‑Fi rapide</span>
            <span>Cuisine équipée</span>
            <span>Salle de bain marbre</span>
            <span>Vue ville</span>
            <span>Arrivée autonome</span>
            <span>Literie hôtelière</span>
          </div>

          <div class="rate-block">
            <div class="rate-row">
              <span>Tarif du jour</span>
              <strong>400 CAD</strong>
            </div>
            <a class="primary-button template-12-button" href="#" data-nd-booking-url>RÉSERVER MAINTENANT</a>
          </div>
        </div>
      </article>
    </section>
  </main>

  <script>
    (function () {
      const slider = document.querySelector('[data-template12-slider]');
      if (!slider) return;

      const track = slider.querySelector('[data-template12-slider-track]');
      const slides = Array.from(slider.querySelectorAll('.template-12-slider__slide'));
      const prev = slider.querySelector('[data-template12-prev]');
      const next = slider.querySelector('[data-template12-next]');
      const dotsWrap = slider.querySelector('[data-template12-dots]');
      let current = 0;

      function goTo(index) {
        current = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${current * 100}%)`;
        dotsWrap.querySelectorAll('button').forEach((dot, i) => {
          dot.classList.toggle('is-active', i === current);
        });
      }

      slides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', `Aller à l\'image ${index + 1}`);
        dot.addEventListener('click', () => goTo(index));
        dotsWrap.appendChild(dot);
      });

      prev.addEventListener('click', () => goTo(current - 1));
      next.addEventListener('click', () => goTo(current + 1));
      goTo(0);
    })();
  </script>
</body>
</html>
