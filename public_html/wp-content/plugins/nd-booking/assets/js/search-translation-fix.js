(function () {
  const placeholderStartPattern = /#TRP[^#>]*>|#!trpst#/gi;
  const placeholderEndPattern = /#TRPEN#|#!trpen#/gi;
  const orphanedMarkerPattern = /#TRP\w*#|#!trp\w*#/gi;
  const wrappedMarkupPattern = /#!trpst#trp-gettext[^#]*#!trpen#(.*?)#!trpst#\/trp-gettext#!trpen#/gis;

  const normalize = (text) => (typeof text === 'string' ? text.replace(/\s+/g, ' ').trim() : '');

  const cleanPlaceholderText = (text) => {
    if (typeof text !== 'string' || (text.indexOf('#TRP') === -1 && text.indexOf('#!trp') === -1)) {
      return text;
    }

    const stripped = text
      .replace(wrappedMarkupPattern, '$1')
      .replace(placeholderStartPattern, '')
      .replace(placeholderEndPattern, '')
      .replace(orphanedMarkerPattern, '')
      .replace(/\s+/g, ' ')
      .trim();

    return stripped;
  };

  const cleanTranslationPlaceholders = () => {
    const root = document.body || document.documentElement;

    if (!root) return;

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) =>
        node.nodeValue &&
        (node.nodeValue.indexOf('#TRP') !== -1 || node.nodeValue.indexOf('#!trp') !== -1)
          ? NodeFilter.FILTER_ACCEPT
          : NodeFilter.FILTER_SKIP,
    });

    const nodesToUpdate = [];

    while (walker.nextNode()) {
      nodesToUpdate.push(walker.currentNode);
    }

    nodesToUpdate.forEach((node) => {
      const cleaned = cleanPlaceholderText(node.nodeValue);

      if (cleaned !== node.nodeValue) {
        node.nodeValue = cleaned;
      }
    });
  };

  const removeScarcityNotices = () => {
    const normalizeForMatch = (text) =>
      normalize(text)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const scarcityPhrases = [
      'last room available',
      'last rooms available',
      'derniere chambre disponible',
      'dernieres chambres disponibles',
    ];

    const walker = document.createTreeWalker(document.body || document.documentElement, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) => {
        if (!node.nodeValue) return NodeFilter.FILTER_SKIP;

        const normalized = normalizeForMatch(node.nodeValue);
        return scarcityPhrases.some((needle) => normalized.includes(needle))
          ? NodeFilter.FILTER_ACCEPT
          : NodeFilter.FILTER_SKIP;
      },
    });

    const nodesToRemove = [];

    while (walker.nextNode()) {
      nodesToRemove.push(walker.currentNode);
    }

    nodesToRemove.forEach((node) => {
      const holder = node.parentElement;

      if (holder) {
        const container =
          holder.closest(
            'button, .nd_booking_btn, .nd_booking_message, .nd_booking_alert, .nd_booking_box, .nd_booking_box_small, .nd_booking_box_large'
          ) || holder;
        container.style.display = 'none';
        container.setAttribute('aria-hidden', 'true');
      } else {
        node.nodeValue = '';
      }
    });
  };

  const runFixes = () => {
    cleanTranslationPlaceholders();
    removeScarcityNotices();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runFixes, { once: true });
  } else {
    runFixes();
  }
})();
