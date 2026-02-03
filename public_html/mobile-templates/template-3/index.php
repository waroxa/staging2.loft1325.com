<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 3</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-3.css" />
</head>
<body>
  <div class="shell">
    <nav class="nav">
      <span>LOFT 1325</span>
      <span>Val-d'Or</span>
    </nav>

    <section class="hero">
      <div class="hero-card">
        <div class="hero-image" id="heroImage"></div>
        <div class="dots" id="heroDots"></div>
        <div class="hero-body">
          <h1>Make the arrival the main event.</h1>
          <p>Luxury lofts with virtual keys, split payments, and photo-ready spaces.</p>
          <div class="hero-buttons">
            <button class="primary" type="button">Book now</button>
            <button class="secondary" type="button">View lofts</button>
          </div>
        </div>
      </div>
    </section>

    <section class="panel">
      <h3>Find your stay</h3>
      <div class="form-grid">
        <div class="field">
          <label>Check-in</label>
          <input type="date" />
        </div>
        <div class="field">
          <label>Check-out</label>
          <input type="date" />
        </div>
        <div class="field">
          <label>Guests</label>
          <select>
            <option>1 guest</option>
            <option>2 guests</option>
            <option>3 guests</option>
            <option>4 guests</option>
            <option>5 guests</option>
          </select>
        </div>
      </div>
      <button class="search-button" type="button">Check availability</button>
      <div class="bubble">Self-serve bills + instant receipts. No waiting, no stress.</div>
    </section>

    <section class="panel">
      <h3>For the friend group that needs wow</h3>
      <div class="tag-row">
        <span class="tag">Instagram-ready</span>
        <span class="tag">Keyless entry</span>
        <span class="tag">Bill splitting</span>
      </div>
      <div class="cards">
        <div class="card">
          <img src="/wp-content/themes/marina/img/4.jpg" alt="Loft lifestyle" />
          <strong>Signature suites</strong>
          <p>Soft textures, curated art, and space to unwind together.</p>
        </div>
        <div class="card">
          <img src="/wp-content/themes/marina/img/9.jpg" alt="Loft lounge" />
          <strong>Late-night ready</strong>
          <p>24/7 virtual concierge and instant support from your phone.</p>
        </div>
      </div>
    </section>

    <section class="cta">
      <h3>Choose the loft. Own the night.</h3>
      <p>Book, pay, and receive your digital key in minutes.</p>
      <button type="button">Reserve now</button>
    </section>
  </div>

  <script>
    // Update the hero slider images here.
    const heroSlides = [
      '/wp-content/themes/marina/img/5.jpg',
      '/wp-content/themes/marina/img/2.jpg',
      '/wp-content/themes/marina/img/7.jpg'
    ];

    const heroImage = document.getElementById('heroImage');
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
      heroImage.style.backgroundImage = `url('${heroSlides[heroIndex]}')`;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 4800);
  </script>
</body>
</html>
