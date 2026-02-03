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
      <div>
        <strong>LOFT 1325</strong>
        <span>Self-serve · Hôtel autonome</span>
      </div>
      <button class="nav-button" type="button">Menu</button>
    </nav>

    <section class="hero">
      <div class="hero-card">
        <div class="hero-image" id="heroImage">
          <span class="hero-label">Hero image placeholder 01</span>
        </div>
        <div class="dots" id="heroDots"></div>
        <div class="hero-body">
          <h1>Make the arrival the main event.</h1>
          <p>Self check-in, digital keys, instant bill payments, and split payments built in.</p>
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
      <div class="bubble">Split bills, pay instantly, and extend nights with one tap.</div>
    </section>

    <section class="panel">
      <div class="insights">
        <div class="insight-card">
          <h3>Sentiment snapshot</h3>
          <ul>
            <li><strong>Tone:</strong> wants more wow and energy.</li>
            <li><strong>Pain points:</strong> must impress affluent friends.</li>
            <li><strong>Desire:</strong> shop/browse rooms before booking.</li>
          </ul>
        </div>
        <div class="insight-card">
          <h3>Design response</h3>
          <ul>
            <li>Polished gradients and bold contrast for glam.</li>
            <li>Sticky navigation and cards for easy browsing.</li>
            <li>Room tiles feel like a premium catalog.</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="panel">
      <h3>Trusted by luxury-loving guests</h3>
      <div class="trust">
        <div class="trust-card">
          <strong>4.9 ★ average</strong>
          <span>“Keyless check-in felt instantly upscale.”</span>
        </div>
        <div class="trust-card">
          <strong>Instant everything</strong>
          <span>Digital keys + instant receipts + split payments.</span>
        </div>
      </div>
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
          <div class="room-image">Room image placeholder</div>
          <strong>Signature suites</strong>
          <p>Soft textures, curated art, and space to unwind together.</p>
        </div>
        <div class="card">
          <div class="room-image">Room image placeholder</div>
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
    const heroSlides = [
      {
        label: 'Hero image placeholder 01',
        background: 'linear-gradient(135deg, #0f172a, #1e293b)'
      },
      {
        label: 'Hero image placeholder 02',
        background: 'linear-gradient(135deg, #1e1b4b, #312e81)'
      },
      {
        label: 'Hero image placeholder 03',
        background: 'linear-gradient(135deg, #111827, #1f2937)'
      }
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
      heroImage.style.backgroundImage = heroSlides[heroIndex].background;
      heroImage.querySelector('.hero-label').textContent = heroSlides[heroIndex].label;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 4800);
  </script>
</body>
</html>
