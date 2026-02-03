<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Loft 1325 Mobile Template 2</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/mobile-templates/assets/template-2.css" />
</head>
<body>
  <div class="wrapper">
    <header class="topbar">
      <div>
        <strong>Loft 1325</strong>
        <span>Virtual Hotel · Hôtel virtuel</span>
      </div>
      <button class="menu" type="button">Menu</button>
    </header>

    <section class="hero">
      <h1>Tap in. Unlock. Wow.</h1>
      <p>Self check-in, digital keys, instant bill payments, and split payments for the whole crew.</p>
      <div class="hero-slider">
        <div class="hero-image" id="heroImage">
          <span class="hero-label">Hero image placeholder 01</span>
        </div>
        <span class="hero-tag">Girls' weekend ready</span>
        <div class="dots" id="heroDots"></div>
      </div>
    </section>

    <div class="card">
      <h3>Reserve your loft</h3>
      <div class="grid">
        <div class="input">
          <label>Arrival</label>
          <input type="date" />
        </div>
        <div class="input">
          <label>Departure</label>
          <input type="date" />
        </div>
        <div class="input">
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
      <button class="cta" type="button">Search availability</button>
      <p>Split payments and view bills instantly. Everything stays in your pocket.</p>
    </div>

    <section class="section">
      <div class="insights">
        <div class="insight-card">
          <h3>Sentiment snapshot</h3>
          <ul>
            <li><strong>Tone:</strong> “not wow,” “feels dead.”</li>
            <li><strong>Pain points:</strong> needs to impress affluent friends.</li>
            <li><strong>Desire:</strong> wants to shop/browse rooms.</li>
          </ul>
        </div>
        <div class="insight-card">
          <h3>Design response</h3>
          <ul>
            <li>Glam gradients and gold accents for instant impact.</li>
            <li>Editorial typography + luxe spacing for prestige.</li>
            <li>Room preview cards like a curated boutique.</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="trust">
        <div class="trust-card">
          <strong>Rated 4.9 by guests</strong>
          <span>“The easiest keyless stay I’ve ever booked.”</span>
        </div>
        <div class="trust-card">
          <strong>Digital concierge</strong>
          <span>24/7 chat, digital keys, and instant receipts.</span>
        </div>
      </div>
    </section>

    <section class="section">
      <h3>What makes it unforgettable</h3>
      <span class="subline">Des moments wow · sans attente</span>
      <div class="story">
        <div class="story-card">
          <strong>Self check-in, elevated</strong>
          <span>Keyless entry and glam lighting ready for content.</span>
        </div>
        <div class="story-card">
          <strong>Instant bill control</strong>
          <span>Pay, split, and extend in seconds. No front desk.</span>
        </div>
        <div class="story-card">
          <strong>Spaces made for photos</strong>
          <span>Designer textures and boutique styling to impress.</span>
        </div>
      </div>
    </section>

    <section class="section">
      <h3>Meet the lofts</h3>
      <div class="gallery">
        <div class="room-card">
          <div class="room-image">Room image placeholder</div>
          <strong>Glow Suite</strong>
          <p>Statement lighting, plush bedding, and mirror moments.</p>
        </div>
        <div class="room-card">
          <div class="room-image">Room image placeholder</div>
          <strong>City Luxe Loft</strong>
          <p>Kitchenette, glam seating, and a view for content.</p>
        </div>
      </div>
    </section>

    <div class="bottom-cta">
      <h3>Book the suite. Bring the girls.</h3>
      <p>Luxury for selfies, convenience for bills, all in one stay.</p>
      <button type="button">Start booking</button>
    </div>
  </div>

  <script>
    const heroSlides = [
      {
        label: 'Hero image placeholder 01',
        background: 'linear-gradient(135deg, #0f172a, #1e3a8a)'
      },
      {
        label: 'Hero image placeholder 02',
        background: 'linear-gradient(135deg, #1f2937, #0f172a)'
      },
      {
        label: 'Hero image placeholder 03',
        background: 'linear-gradient(135deg, #1e1b4b, #0f172a)'
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
    }, 4500);
  </script>
</body>
</html>
