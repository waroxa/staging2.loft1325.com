(function () {
  function moveTrustindexToEnd() {
    var main = document.querySelector('[data-booking-main-content]');
    var end = document.querySelector('[data-booking-trustindex-end]');

    if (!main || !end) {
      return;
    }

    var selector = [
      '[class*="trustindex"]',
      '[id*="trustindex"]',
      'iframe[src*="trustindex"]'
    ].join(',');

    var nodes = main.querySelectorAll(selector);
    if (!nodes.length) {
      return;
    }

    nodes.forEach(function (node) {
      end.appendChild(node);
    });
  }

  document.addEventListener('DOMContentLoaded', moveTrustindexToEnd);
})();
