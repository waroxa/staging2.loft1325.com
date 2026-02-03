<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 4</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-4.css" />
</head>
<body>
  <div class="frame">
    <header class="topbar">
      <div class="brand">
        <span>LOFT 1325</span>
        <small>Virtual Hotel · Hôtel autonome</small>
      </div>
      <button class="icon-button" type="button">≡</button>
    </header>

    <section class="hero">
      <div class="hero-slider" id="heroSlider">
        <div class="hero-panel" id="heroPanel">
          <span class="hero-label">Hero image placeholder 01</span>
        </div>
        <div class="hero-dots" id="heroDots"></div>
      </div>
      <div class="hero-copy">
        <h1>Arrive glam. Leave obsessed.</h1>
        <p>Self check-in, digital keys, instant bill payments, and split payments — effortless luxury.</p>
        <div class="hero-actions">
          <button class="primary" type="button">Book your stay</button>
          <button class="ghost" type="button">Browse rooms</button>
        </div>
      </div>
    </section>

    <section class="booking">
      <div class="booking-card">
        <h3>Book your loft</h3>
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
        <button class="accent" type="button">Check availability</button>
        <p class="note">Instant receipts, split payments, and no front-desk wait.</p>
      </div>
    </section>

    <section class="insights">
      <div class="insight">
        <h3>Sentiment snapshot</h3>
        <ul>
          <li><strong>Tone:</strong> “not wow,” “feels dead.”</li>
          <li><strong>Pain points:</strong> friends are rich, needs to impress.</li>
          <li><strong>Desire:</strong> browse rooms like shopping.</li>
        </ul>
      </div>
      <div class="insight">
        <h3>Design response</h3>
        <ul>
          <li>High-contrast editorial type for luxe energy.</li>
          <li>Gold accents and soft glow shadows for glamour.</li>
          <li>Room cards like a curated boutique feed.</li>
        </ul>
      </div>
    </section>

    <section class="trust">
      <div class="trust-card">
        <strong>4.9 ★ guest love</strong>
        <span>“It felt like a private club, but effortless.”</span>
      </div>
      <div class="trust-card">
        <strong>Digital concierge</strong>
        <span>Keyless entry + instant bills + split payments.</span>
      </div>
    </section>

    <section class="features">
      <h3>Feature highlights</h3>
      <span class="subline">Moments wow · sans effort</span>
      <div class="feature-grid">
        <div class="feature">
          <strong>Self check-in, always ready</strong>
          <p>Arrive late, unlock instantly, and skip the line.</p>
        </div>
        <div class="feature">
          <strong>Digital keys + instant payments</strong>
          <p>Pay or split in seconds. Receipts land immediately.</p>
        </div>
        <div class="feature">
          <strong>Shop the vibe</strong>
          <p>Swipe through room styles before you book.</p>
        </div>
      </div>
    </section>

    <section class="rooms">
      <h3>Room preview</h3>
      <div class="room-card">
        <div class="room-image">Room image placeholder</div>
        <div>
          <strong>Velvet Glow Loft</strong>
          <p>Statement lighting, plush bedding, and mirror moments.</p>
        </div>
      </div>
      <div class="room-card">
        <div class="room-image">Room image placeholder</div>
        <div>
          <strong>Champagne Suite</strong>
          <p>Soft neutrals, glam seating, and boutique styling.</p>
        </div>
      </div>
    </section>

    <section class="cta">
      <h3>Ready for your wow moment?</h3>
      <p>Book now, split the bill, and unlock instantly.</p>
      <button type="button">Start booking</button>
    </section>
  </div>

  <script>
    const heroSlides = [
      {
        label: 'Hero image placeholder 01',
        background: 'linear-gradient(135deg, #111827, #1f2937)'
      },
      {
        label: 'Hero image placeholder 02',
        background: 'linear-gradient(135deg, #1f2937, #312e81)'
      },
      {
        label: 'Hero image placeholder 03',
        background: 'linear-gradient(135deg, #0f172a, #475569)'
      }
    ];

    const heroPanel = document.getElementById('heroPanel');
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
      heroPanel.style.backgroundImage = heroSlides[heroIndex].background;
      heroPanel.querySelector('.hero-label').textContent = heroSlides[heroIndex].label;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 4700);
  </script>
</body>
</html>
