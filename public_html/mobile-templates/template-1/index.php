<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 1</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-1.css" />
</head>
<body>
  <div class="page">
    <header class="topbar">
      <div class="logo">LOFT 1325</div>
      <div class="pill">Self-Serve Hotel · Hôtel autonome</div>
    </header>

    <section class="hero">
      <div class="hero-card">
        <div class="hero-slide" id="heroSlide">
          <span class="hero-label">Hero image placeholder 01</span>
        </div>
        <div class="slider-dots" id="heroDots"></div>
        <div class="hero-content">
          <h1>Luxury stays, zero waiting.</h1>
          <p>Self check-in, digital keys, instant bill payments, and split-the-bill ease.</p>
          <span class="subline">Arrivée autonome · clés numériques instantanées</span>
          <div class="hero-actions">
            <button class="button-primary" type="button">Book in 60 sec</button>
            <button class="button-secondary" type="button">Explore rooms</button>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="booking-card">
        <div class="booking-grid">
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
        <button class="booking-submit" type="button">Search lofts</button>
        <p class="note">Pay your stay, extend nights, and split payments from your phone.</p>
      </div>
    </section>

    <section class="section">
      <div class="insights">
        <div class="insight-card">
          <h3>Sentiment snapshot</h3>
          <ul>
            <li><strong>Tone:</strong> yearning for “wow,” feels flat or “dead.”</li>
            <li><strong>Pain points:</strong> needs to impress affluent friends; wants more glam.</li>
            <li><strong>Desire:</strong> wants to browse rooms like shopping.</li>
          </ul>
        </div>
        <div class="insight-card">
          <h3>Design response</h3>
          <ul>
            <li>Hero imagery + glow gradients create instant wow.</li>
            <li>Luxury cues: elevated typography, gold accents, soft shadows.</li>
            <li>Room previews feel like a boutique catalog.</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="trust">
        <div class="trust-card">
          <strong>Google Reviews 4.9 ★</strong>
          <span>“The most photogenic self-check-in I’ve tried.”</span>
        </div>
        <div class="trust-card">
          <strong>Digital-first stay</strong>
          <span>Keyless entry, instant receipts, and split payments built in.</span>
        </div>
      </div>
    </section>

    <section class="section">
      <h3>Why her friends will say WOW</h3>
      <span class="subline">Des moments qui impressionnent · tout en simplicité</span>
      <div class="feature-list">
        <div class="feature">
          <span>01</span>
          <div>
            <strong>Arrive like a VIP</strong>
            <p>Self check-in, digital keys, and glam lighting for content-ready moments.</p>
          </div>
        </div>
        <div class="feature">
          <span>02</span>
          <div>
            <strong>Instant bills + split payments</strong>
            <p>Pay, extend, and split costs without waiting for a front desk.</p>
          </div>
        </div>
        <div class="feature">
          <span>03</span>
          <div>
            <strong>Shop the rooms</strong>
            <p>Swipeable previews to pick your perfect glam vibe.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="room-card">
        <div class="room-image">Room image placeholder</div>
        <h4>Signature Loft · Penthouse Energy</h4>
        <p>Plush bedding, curated art, and statement lighting for wow-worthy arrivals.</p>
        <button class="button-primary" type="button">See all lofts</button>
      </div>
    </section>

    <section class="section">
      <div class="footer-cta">
        <h3>Ready for the wow moment?</h3>
        <p>Book, pay, and receive your digital key in minutes.</p>
        <button type="button">Start booking</button>
      </div>
    </section>
  </div>

  <script>
    const heroSlides = [
      {
        label: 'Hero image placeholder 01',
        background: 'linear-gradient(135deg, #0f172a, #334155)'
      },
      {
        label: 'Hero image placeholder 02',
        background: 'linear-gradient(135deg, #1e1b4b, #312e81)'
      },
      {
        label: 'Hero image placeholder 03',
        background: 'linear-gradient(135deg, #0f172a, #475569)'
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
      heroSlide.querySelector('.hero-label').textContent = heroSlides[heroIndex].label;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 5000);
  </script>
</body>
</html>
