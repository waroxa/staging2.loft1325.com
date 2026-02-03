<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 6</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-6.css" />
</head>
<body>
  <div class="mobile-page">
    <section class="hero" aria-label="Galerie principale">
      <div class="hero-slider">
        <div class="hero-slide" id="heroSlide">
          <div class="hero-overlay">
            <span class="hero-title">LOFT 1325</span>
            <span class="hero-tagline">Votre refuge privé en ville</span>
          </div>
        </div>
        <div class="hero-dots" id="heroDots" aria-hidden="true"></div>
      </div>
    </section>

    <section class="location" aria-label="Adresse">
      <span class="meta">22 lofts · Québec</span>
      <p class="address">1325 rue Saint-Louis, Vieux-Québec</p>
      <div class="divider"></div>
    </section>

    <section class="booking" aria-label="Module de réservation">
      <div class="booking-card">
        <div class="booking-fields">
          <label class="field">
            <span>Arrivée</span>
            <input type="date" />
          </label>
          <label class="field">
            <span>Départ</span>
            <input type="date" />
          </label>
          <label class="field">
            <span>Invitées</span>
            <select>
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5+</option>
            </select>
          </label>
        </div>
        <button class="cta" type="button">Découvrir les disponibilités</button>
        <button class="ghost" type="button">Entrer</button>
      </div>
    </section>
  </div>

  <script>
    const heroSlides = [
      {
        image:
          'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1400&q=80',
        label: 'Salon doré'
      },
      {
        image:
          'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
        label: 'Suite lumineuse'
      },
      {
        image:
          'https://images.unsplash.com/photo-1502005229762-cf1b2da7c5d6?auto=format&fit=crop&w=1400&q=80',
        label: 'Loft privé'
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
      heroSlide.style.backgroundImage = `linear-gradient(180deg, rgba(18, 13, 8, 0.1), rgba(18, 13, 8, 0.6)), url('${heroSlides[heroIndex].image}')`;
      heroSlide.dataset.label = heroSlides[heroIndex].label;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 5200);
  </script>
</body>
</html>
