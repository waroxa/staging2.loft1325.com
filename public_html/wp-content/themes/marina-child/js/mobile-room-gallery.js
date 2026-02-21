(function () {
  var root = document.querySelector('[data-room-gallery]');
  if (!root) return;

  var images = [];
  try {
    images = JSON.parse(root.getAttribute('data-room-gallery') || '[]');
  } catch (e) {
    images = [];
  }

  if (!images.length) return;

  var imageEl = root.querySelector('[data-gallery-image]');
  var dots = root.querySelector('[data-gallery-dots]');
  var thumbs = root.querySelector('[data-gallery-thumbs]');
  var prev = root.querySelector('[data-gallery-prev]');
  var next = root.querySelector('[data-gallery-next]');
  var index = 0;
  var touchStartX = 0;
  var touchEndX = 0;

  function renderNav() {
    dots.innerHTML = '';
    thumbs.innerHTML = '';

    images.forEach(function (src, i) {
      var dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'room-gallery__dot' + (i === index ? ' is-active' : '');
      dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
      dot.addEventListener('click', function () { setIndex(i); });
      dots.appendChild(dot);

      var thumb = document.createElement('button');
      thumb.type = 'button';
      thumb.className = 'room-gallery__thumb' + (i === index ? ' is-active' : '');
      thumb.innerHTML = '<img src="' + src + '" loading="lazy" alt="Thumbnail ' + (i + 1) + '">';
      thumb.addEventListener('click', function () { setIndex(i); });
      thumbs.appendChild(thumb);
    });
  }

  function setIndex(i) {
    index = (i + images.length) % images.length;
    imageEl.src = images[index];
    renderNav();
  }

  prev.addEventListener('click', function () { setIndex(index - 1); });
  next.addEventListener('click', function () { setIndex(index + 1); });

  imageEl.addEventListener('touchstart', function (event) {
    touchStartX = event.changedTouches[0].screenX;
  }, { passive: true });

  imageEl.addEventListener('touchend', function (event) {
    touchEndX = event.changedTouches[0].screenX;
    if (Math.abs(touchEndX - touchStartX) < 40) return;
    if (touchEndX < touchStartX) setIndex(index + 1);
    if (touchEndX > touchStartX) setIndex(index - 1);
  }, { passive: true });

  setIndex(0);
})();
