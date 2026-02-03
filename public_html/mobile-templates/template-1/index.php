<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 1</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-1.css" />
</head>
<body>
  <div class="page">
    <section class="hero">
      <div class="hero-slider">
        <div class="hero-slide" id="heroSlide">
          <div class="hero-overlay">
            <p class="hero-brand">LOFT 1325</p>
            <h1 class="hero-title">Maison de 22 lofts · Québec</h1>
            <p class="hero-subline">Choisi par celles qui savent</p>
          </div>
        </div>
        <div class="slider-dots" id="heroDots"></div>
      </div>
    </section>

    <section class="meta">
      <p class="meta-address">1325 Rue Saint-Antoine Ouest, Montréal</p>
      <div class="meta-proof">
        <span class="meta-pill">Favori des séjours privés à Montréal</span>
      </div>
    </section>

    <section class="booking">
      <div class="booking-card">
        <div class="booking-row">
          <label class="booking-field">
            <span>Arrivée</span>
            <input type="date" />
          </label>
          <label class="booking-field">
            <span>Départ</span>
            <input type="date" />
          </label>
          <label class="booking-field">
            <span>Invités</span>
            <select>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
            </select>
          </label>
        </div>
        <button class="booking-cta" type="button">Réserver maintenant</button>
      </div>
    </section>

    <section class="stories">
      <div class="stories-header">
        <h2>Moments en cercle privé</h2>
        <a class="stories-link" href="https://staging2.loft1325.com/rooms">Voir les lofts</a>
      </div>
      <div class="story-scroll">
        <article class="story-card">
          <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=600&q=80" alt="Entre amies" />
          <span>Entre amies</span>
        </article>
        <article class="story-card">
          <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" alt="Week-ends privés" />
          <span>Week-ends privés</span>
        </article>
        <article class="story-card">
          <img src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=600&q=80" alt="Moments iconiques" />
          <span>Moments iconiques</span>
        </article>
      </div>
    </section>

    <section class="footer-links">
      <a href="https://staging2.loft1325.com/booking">Réservation</a>
      <a href="https://staging2.loft1325.com/experiences">Expériences</a>
      <a href="https://staging2.loft1325.com/contact">Contact</a>
    </section>
  </div>

  <script>
    const heroSlides = [
      {
        background: 'url("https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=1200&q=80")'
      },
      {
        background: 'url("https://images.unsplash.com/photo-1519822474541-6e1968e6ec6f?auto=format&fit=crop&w=1200&q=80")'
      },
      {
        background: 'url("https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1200&q=80")'
      }
    ];

    const heroSlide = document.getElementById('heroSlide');
    const heroDots = document.getElementById('heroDots');
    let heroIndex = 0;

    function renderDots() {
      heroDots.innerHTML = '';
      heroSlides.forEach((_, index) => {
        const dot = document.createElement('span');
        if (index === heroIndex) {
          dot.classList.add('active');
        }
        heroDots.appendChild(dot);
      });
    }

    function showSlide(index) {
      heroIndex = index % heroSlides.length;
      heroSlide.style.backgroundImage = heroSlides[heroIndex].background;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 4500);
  </script>
</body>
</html>
