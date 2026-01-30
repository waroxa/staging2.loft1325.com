(() => {
  const masonrySelector = '.nd_booking_masonry_content';
  const itemSelector = '.nd_booking_masonry_item';
  const containerSelector = '#nd_booking_archive_search_masonry_container';
  const mobileQuery = window.matchMedia('(max-width: 768px)');
  let resetQueued = false;

  const resetMasonryLayout = () => {
    resetQueued = false;

    if (!mobileQuery.matches) {
      return;
    }

    const contents = document.querySelectorAll(masonrySelector);
    if (!contents.length) {
      return;
    }

    contents.forEach((content) => {
      if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.masonry === 'function') {
        const $content = window.jQuery(content);
        if ($content.data('masonry')) {
          $content.masonry('destroy');
        }
      }

      content.style.height = 'auto';

      content.querySelectorAll(itemSelector).forEach((item) => {
        item.style.removeProperty('position');
        item.style.removeProperty('left');
        item.style.removeProperty('top');
        item.style.removeProperty('width');
      });
    });
  };

  const scheduleReset = () => {
    if (resetQueued) {
      return;
    }

    resetQueued = true;
    window.requestAnimationFrame(resetMasonryLayout);
  };

  const watchForUpdates = () => {
    const container = document.querySelector(containerSelector);
    if (!container || typeof MutationObserver === 'undefined') {
      return;
    }

    const observer = new MutationObserver((mutations) => {
      if (mutations.some((mutation) => mutation.addedNodes.length || mutation.removedNodes.length)) {
        scheduleReset();
      }
    });

    observer.observe(container, { childList: true, subtree: true });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      scheduleReset();
      watchForUpdates();
    });
  } else {
    scheduleReset();
    watchForUpdates();
  }

  document.addEventListener('ndBooking:hideResultsLoader', scheduleReset);
  window.addEventListener('resize', scheduleReset);
  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', scheduleReset);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(scheduleReset);
  }
})();
