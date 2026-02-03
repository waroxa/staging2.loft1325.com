(function () {
  var sliders = document.querySelectorAll('[data-loft-slider]');

  if (!sliders.length) {
    return;
  }

  var setupSlider = function (slider) {
    var imagesData = slider.getAttribute('data-images');
    var interval = parseInt(slider.getAttribute('data-interval') || '6000', 10);
    var media = slider.querySelector('.loft-hero__media');
    var currentIndex = 0;
    var images = [];

    try {
      images = JSON.parse(imagesData || '[]');
    } catch (error) {
      images = [];
    }

    if (!media || !images.length) {
      return;
    }

    var setSlide = function (index) {
      currentIndex = (index + images.length) % images.length;
      media.style.backgroundImage = 'url(' + images[currentIndex] + ')';
    };

    setSlide(0);

    var nextButton = slider.querySelector('[data-loft-next]');
    var prevButton = slider.querySelector('[data-loft-prev]');

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        setSlide(currentIndex + 1);
      });
    }

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        setSlide(currentIndex - 1);
      });
    }

    if (interval > 0) {
      setInterval(function () {
        setSlide(currentIndex + 1);
      }, interval);
    }
  };

  sliders.forEach(setupSlider);
})();
