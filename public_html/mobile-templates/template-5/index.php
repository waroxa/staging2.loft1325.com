<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 5</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-5.css" />
</head>
<body>
  <div class="mobile-shell">
    <header class="header">
      <button class="icon-button" type="button" aria-label="Retour">←</button>
      <span class="header-title">LOFT 1325</span>
      <button class="icon-button" type="button" aria-label="Menu">≡</button>
    </header>

    <section class="hero" aria-label="Galerie principale">
      <div class="hero-slider" id="heroSlider">
        <div class="hero-slide" id="heroSlide">
          <div class="hero-overlay">
            <span class="hero-label">Loft 1325</span>
            <span class="hero-city">Québec · Canada</span>
          </div>
        </div>
        <div class="hero-dots" id="heroDots"></div>
      </div>
      <div class="hero-meta">
        <h1>Résidence privée · 22 lofts</h1>
        <p class="address">VIEUX-QUÉBEC · 1325 RUE SAINT-LOUIS</p>
      </div>
    </section>

    <section class="booking" aria-label="Module de réservation">
      <div class="booking-row">
        <label class="field">
          <span>Arrivée</span>
          <input type="date" />
        </label>
        <label class="field">
          <span>Départ</span>
          <input type="date" />
        </label>
        <label class="field">
          <span>Voyageurs</span>
          <select>
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
          </select>
        </label>
      </div>
      <button class="cta" type="button">Vérifier la disponibilité</button>
    </section>

    <section class="highlights" aria-label="Signature">
      <div class="divider"></div>
      <p>
        Silence feutré, service discret, intérieurs en pierre claire et bois fumé.
        Une adresse confidentielle, pensée comme un club privé.
      </p>
      <div class="divider"></div>
    </section>
  </div>

  <script>
    const heroSlides = [
      {
        image:
          'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1200&q=80',
        label: 'Salon privé'
      },
      {
        image:
          'https://images.unsplash.com/photo-1502005229762-cf1b2da7c5d6?auto=format&fit=crop&w=1200&q=80',
        label: 'Suite en pierre'
      },
      {
        image:
          'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        label: 'Vue sur la cour'
      }
    ];

    const heroSlide = document.getElementById('heroSlide');
    const heroDots = document.getElementById('heroDots');
    let heroIndex = 0;

    function renderDots() {
      heroDots.innerHTML = '';
      heroSlides.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        if (index === heroIndex) {
          dot.classList.add('active');
        }
        dot.addEventListener('click', () => {
          showSlide(index);
        });
        heroDots.appendChild(dot);
      });
    }

    function showSlide(index) {
      heroIndex = index % heroSlides.length;
      heroSlide.style.backgroundImage = `url(${heroSlides[heroIndex].image})`;
      heroSlide.setAttribute('aria-label', heroSlides[heroIndex].label);
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 5200);
  </script>
</body>
</html>
