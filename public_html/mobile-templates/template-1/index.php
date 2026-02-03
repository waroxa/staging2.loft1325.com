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
      <div class="pill">Self-Serve Hotel</div>
    </header>

    <section class="hero">
      <div class="hero-card">
        <div class="hero-slide" id="heroSlide"></div>
        <div class="slider-dots" id="heroDots"></div>
        <div class="hero-content">
          <h1>Luxury stays, zero waiting.</h1>
          <p>Private lofts in Val-d'Or with digital keys, in-room workspaces, and a wow-worthy vibe.</p>
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
        <p class="note">Pay your stay, extend nights, and handle bills straight from your phone.</p>
      </div>
    </section>

    <section class="section">
      <div class="trust">
        <div class="trust-card">
          <strong>Google Reviews 4.9 ★</strong>
          <span>Guests love the clean design + instant check-in.</span>
        </div>
        <div class="trust-card">
          <strong>Self-Serve Experience</strong>
          <span>Digital keys, virtual concierge, and receipts in one tap.</span>
        </div>
      </div>
    </section>

    <section class="section">
      <h3>Why her friends will say WOW</h3>
      <div class="feature-list">
        <div class="feature">
          <span>01</span>
          <div>
            <strong>Arrive like a VIP</strong>
            <p>Smart entry, mood lighting, and curated playlists ready for photos.</p>
          </div>
        </div>
        <div class="feature">
          <span>02</span>
          <div>
            <strong>Pay & extend instantly</strong>
            <p>No front desk. Add nights or pay bills on your phone.</p>
          </div>
        </div>
        <div class="feature">
          <span>03</span>
          <div>
            <strong>Location of choice</strong>
            <p>Stay steps from dining, shops, and the city lights.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="room-card">
        <img src="/wp-content/themes/marina/img/3.jpg" alt="Loft interior" />
        <h4>Signature Loft + Penthouse Energy</h4>
        <p>Bright, modern suites with plush bedding, kitchenettes, and luxury touches.</p>
        <button class="button-primary" type="button">See all lofts</button>
      </div>
    </section>

    <section class="section">
      <div class="footer-cta">
        <h3>Ready for the wow moment?</h3>
        <p>Book the stay, split the bill, and arrive hands-free.</p>
        <button type="button">Start booking</button>
      </div>
    </section>
  </div>

  <script>
    // Update the hero slider images here.
    const heroSlides = [
      '/wp-content/themes/marina/img/1.jpg',
      '/wp-content/themes/marina/img/5.jpg',
      '/wp-content/themes/marina/img/7.jpg'
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
      heroSlide.style.backgroundImage = `url('${heroSlides[heroIndex]}')`;
      renderDots();
    }

    showSlide(heroIndex);
    setInterval(() => {
      showSlide(heroIndex + 1);
    }, 5000);
  </script>
</body>
</html>
