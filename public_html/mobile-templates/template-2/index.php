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
    <header class="header">
      <span>Loft 1325 · Virtual Hotel</span>
      <h1>Tap in. Unlock. Wow.</h1>
      <p>Designed for modern travelers who want a glamorous stay without the front desk.</p>
      <div class="hero-slider">
        <div class="hero-image" id="heroImage"></div>
        <span class="hero-tag">Girls' weekend ready</span>
        <div class="dots" id="heroDots"></div>
      </div>
    </header>

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
      <h3>What makes it unforgettable</h3>
      <div class="story">
        <div class="story-card">
          <strong>Self check-in, elevated</strong>
          <span>Keyless entry, late-night access, and concierge messaging 24/7.</span>
        </div>
        <div class="story-card">
          <strong>Spaces made for photos</strong>
          <span>Designer furniture, soft lighting, and balcony views to impress your crew.</span>
        </div>
        <div class="story-card">
          <strong>Pay + extend with a tap</strong>
          <span>Manage bills, extend nights, and add services without a call.</span>
        </div>
      </div>
    </section>

    <section class="section">
      <h3>Meet the lofts</h3>
      <div class="gallery">
        <img src="/wp-content/themes/marina/img/6.jpg" alt="Loft living room" />
        <img src="/wp-content/themes/marina/img/8.jpg" alt="Loft bedroom" />
      </div>
    </section>

    <div class="bottom-cta">
      <h3>Book the suite. Bring the girls.</h3>
      <p>Luxury for selfies, convenience for bills, all in one stay.</p>
      <button type="button">Start booking</button>
    </div>
  </div>

  <script>
    // Update the hero slider images here.
    const heroSlides = [
      '/wp-content/themes/marina/img/2.jpg',
      '/wp-content/themes/marina/img/4.jpg',
      '/wp-content/themes/marina/img/9.jpg'
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
    }, 4500);
  </script>
</body>
</html>
