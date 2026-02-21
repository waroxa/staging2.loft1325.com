<?php
/**
 * Mobile-only front page template.
 *
 * @package Loft1325\MobileHomepage
 */

defined( 'ABSPATH' ) || exit;

$locale   = get_locale();
$language = strpos( $locale, 'en_' ) === 0 ? 'en' : 'fr';
$strings  = array(
    'menu_label'             => $language === 'en' ? 'Open menu' : 'Ouvrir le menu',
    'menu_close'             => $language === 'en' ? 'Close menu' : 'Fermer le menu',
    'menu_title'             => $language === 'en' ? 'Menu' : 'Menu',
    'hero_title'             => $language === 'en' ? 'Select a room' : 'Sélectionner une chambre',
    'hero_tagline'           => $language === 'en' ? 'VIRTUAL HOTEL EXPERIENCE' : 'EXPÉRIENCE HÔTELIÈRE 100 % VIRTUELLE',
    'search_title'           => $language === 'en' ? 'SEARCH' : 'RECHERCHER',
    'dates_tile_label'       => $language === 'en' ? 'Dates' : 'Dates',
    'guests_tile_label'      => $language === 'en' ? 'Guests' : 'Voyageurs',
    'date_placeholder'       => $language === 'en' ? 'Add dates' : 'Ajouter des dates',
    'date_range_placeholder' => $language === 'en' ? 'Select your dates' : 'Sélectionnez vos dates',
    'guest_placeholder'      => $language === 'en' ? 'Add guests' : 'Ajouter des voyageurs',
    'dates_label'            => $language === 'en' ? 'DATES' : 'DATES',
    'guests_label'           => $language === 'en' ? 'GUESTS' : 'CLIENTÈLE VOYAGEURS',
    'adults_label'           => $language === 'en' ? 'Adults (Ages 18 or above)' : 'Adultes (18 ans ou plus)',
    'children_label'         => $language === 'en' ? 'Children (Ages 0-17)' : 'Enfants (0-17 ans)',
    'no_checkin'             => $language === 'en' ? 'No check-in' : "Pas d'enregistrement",
    'no_checkout'            => $language === 'en' ? 'No check-out' : 'Pas de départ',
    'summary_sub'            => $language === 'en' ? 'Excluding taxes and fees' : 'Hors taxes et frais',
    'cta'                    => $language === 'en' ? 'SEARCH' : 'RECHERCHE',
    'finalize_cta'           => $language === 'en' ? 'Finalize' : 'Finaliser',
    'sticky_note'            => $language === 'en' ? 'You found the best rate.' : 'Vous avez trouvé le meilleur prix.',
    'adult_singular'         => $language === 'en' ? 'adult' : 'adulte',
    'adult_plural'           => $language === 'en' ? 'adults' : 'adultes',
    'child_singular'         => $language === 'en' ? 'child' : 'enfant',
    'child_plural'           => $language === 'en' ? 'children' : 'enfants',
    'error_unavailable'      => $language === 'en' ? 'Selected dates include unavailable nights.' : 'Les dates choisies incluent des nuits indisponibles.',
    'summary_template'       => $language === 'en' ? 'From %1$s CA$ total for %2$s nights' : 'A partir de %1$s $CA total pour %2$s nuits',
    'summary_template_empty' => $language === 'en' ? 'From %1$s CA$ total for %2$s night' : 'A partir de %1$s $CA total pour %2$s nuit',
    'month_label'            => $language === 'en' ? 'FEBRUARY 2026' : 'FÉVRIER 2026',
    'next_month'             => $language === 'en' ? 'Next month' : 'Mois suivant',
    'close'                  => $language === 'en' ? 'Close' : 'Fermer',
);

?><!DOCTYPE html>
<html lang="<?php echo esc_attr( $language ); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lofts 1325 · Mobile Booking</title>
  <?php wp_head(); ?>
  <style>
    :root {
      color-scheme: light;
      --black: #0b0b0b;
      --white: #ffffff;
      --gray-100: #f5f5f5;
      --gray-200: #e5e5e5;
      --gray-300: #d7d7d7;
      --gray-500: #7a7a7a;
      --shadow: 0 18px 32px rgba(0, 0, 0, 0.08);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Inter", "Helvetica Neue", sans-serif;
      background: var(--white);
      color: var(--black);
    }

    .mobile-shell {
      max-width: 430px;
      margin: 0 auto;
      min-height: 100vh;
      background: var(--white);
      display: flex;
      flex-direction: column;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 10;
      background: var(--white);
      border-bottom: 1px solid var(--gray-200);
    }

    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
    }

    .logo {
      height: 26px;
      width: auto;
    }

    .icon-button {
      border: 1px solid var(--black);
      background: transparent;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      line-height: 1;
      color: var(--black);
    }

    .header .icon-button {
      border: none;
      border-radius: 0;
      width: auto;
      height: auto;
      min-width: 28px;
    }

    .language-toggle {
      width: auto;
      min-width: 74px;
      padding: 0;
      gap: 8px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .language-toggle__label {
      opacity: 0.45;
    }

    .language-toggle__label.is-active {
      opacity: 1;
    }

    .mobile-menu {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      display: none;
      z-index: 30;
    }

    .mobile-menu.is-open {
      display: block;
    }

    .mobile-menu__panel {
      background: var(--white);
      height: 100%;
      width: min(320px, 84%);
      padding: 22px 20px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      box-shadow: var(--shadow);
    }

    .mobile-menu__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .mobile-menu__title {
      font-size: 14px;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .mobile-menu__close {
      border: none;
      background: transparent;
      font-size: 26px;
      line-height: 1;
    }

    .mobile-menu__list {
      list-style: none;
      display: grid;
      gap: 12px;
      font-size: 16px;
    }

    .mobile-menu__list a {
      color: var(--black);
      text-decoration: none;
      font-weight: 500;
    }

    .hero {
      padding: 20px;
      background: var(--gray-100);
      border-bottom: 1px solid var(--gray-200);
    }

    .hero h1 {
      font-family: "Playfair Display", serif;
      font-size: 20px;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      margin-bottom: 6px;
      white-space: nowrap;
    }

    .hero p {
      font-size: 14px;
      color: var(--gray-500);
    }

    .hero-tagline {
      font-family: "Playfair Display", serif;
      font-size: 18px;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      line-height: 1.1;
      color: var(--black);
      margin-bottom: 12px;
      white-space: nowrap;
    }

    .search-panel {
      margin-top: 12px;
      display: grid;
      gap: 12px;
    }

    .search-tile {
      border: 1px solid var(--black);
      padding: 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      background: var(--white);
    }

    .search-tile span {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
    }

    .search-tile strong {
      font-size: 15px;
      letter-spacing: 0.03em;
      white-space: nowrap;
    }

    .room-list {
      padding: 20px;
      display: grid;
      gap: 20px;
    }

    .room-card {
      border: 1px solid var(--gray-200);
      background: var(--white);
      box-shadow: var(--shadow);
    }

    .room-card img {
      width: 100%;
      height: 210px;
      object-fit: cover;
      display: block;
    }

    .room-body {
      padding: 16px;
      display: grid;
      gap: 12px;
    }

    .room-title {
      font-size: 20px;
      font-weight: 600;
    }

    .room-meta {
      font-size: 13px;
      color: var(--gray-500);
    }

    .room-features {
      font-size: 13px;
      line-height: 1.6;
    }

    .restaurant-section {
      padding: 20px;
      display: grid;
      gap: 14px;
    }

    .restaurant-section h2 {
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      margin-bottom: 4px;
    }

    .restaurant-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .restaurant-card {
      border: 1px solid var(--gray-200);
      background: var(--white);
      display: grid;
      place-items: center;
      padding: 12px;
      min-height: 110px;
    }

    .restaurant-card img {
      width: 100%;
      height: auto;
      max-height: 70px;
      object-fit: contain;
    }

    .rate-block {
      border-top: 1px solid var(--gray-200);
      padding-top: 12px;
      display: grid;
      gap: 10px;
    }

    .rate-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      font-size: 14px;
    }

    .rate-row strong {
      font-size: 18px;
    }

    .primary-button {
      width: 100%;
      padding: 12px 14px;
      background: var(--black);
      color: var(--white);
      border: 1px solid var(--black);
      text-transform: uppercase;
      letter-spacing: 0.2em;
      font-size: 12px;
    }

    .sticky-bar {
      position: sticky;
      bottom: 0;
      z-index: 8;
      border-top: 1px solid var(--gray-200);
      background: var(--white);
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
    }

    .sticky-price {
      font-size: 16px;
      font-weight: 600;
    }

    .sticky-note {
      font-size: 12px;
      color: var(--gray-500);
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.55);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 20;
    }

    .modal.active {
      display: flex;
    }

    .dates-modal .modal-content {
      width: 100%;
      max-width: 430px;
      height: 100%;
      background: var(--white);
      display: flex;
      flex-direction: column;
      gap: 24px;
      padding: 18px 18px 140px;
      overflow-y: auto;
    }

    .dates-modal__header {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      position: relative;
      padding-top: 6px;
    }

    .dates-modal__header h2 {
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.2em;
      justify-self: center;
    }

    .dates-modal__header .icon-button {
      justify-self: end;
      border: none;
      font-size: 22px;
    }

    .dates-modal__section {
      display: grid;
      gap: 16px;
    }

    .dates-modal__section-title {
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .dates-modal__month {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.08em;
    }

    .dates-modal__month-label {
      justify-self: center;
      text-align: center;
    }

    .dates-modal__chevron {
      border: none;
      background: transparent;
      font-size: 28px;
      line-height: 1;
      color: var(--black);
      justify-self: end;
    }

    .calendar {
      display: grid;
      gap: 12px;
    }

    .calendar-weekdays,
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
    }

    .calendar-weekdays span {
      font-size: 12px;
      letter-spacing: 0.1em;
      color: var(--gray-500);
    }

    .calendar-day {
      border: 1px solid var(--gray-200);
      background: var(--white);
      aspect-ratio: 1 / 1;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: flex-start;
      gap: 4px;
      padding: 6px;
      position: relative;
      font-size: 14px;
      color: var(--black);
    }

    .calendar-day.is-empty {
      border: none;
      background: transparent;
    }

    .calendar-day .day-number {
      font-size: 14px;
      font-weight: 600;
    }

    .calendar-day .day-price {
      font-size: 11px;
      color: var(--gray-500);
    }

    .calendar-day.is-range {
      background: var(--black);
      color: var(--white);
    }

    .calendar-day.is-range .day-price {
      color: var(--white);
    }

    .calendar-day.is-start .day-price,
    .calendar-day.is-end .day-price {
      color: var(--white);
    }

    .calendar-day.is-start,
    .calendar-day.is-end {
      background: var(--black);
      color: var(--white);
      z-index: 1;
    }

    .calendar-day.is-start.is-range,
    .calendar-day.is-end.is-range {
      background: var(--black);
      color: var(--white);
    }

    .calendar-day.is-active-end {
      box-shadow: inset 0 0 0 2px var(--white);
    }

    .calendar-day.is-disabled {
      color: var(--gray-500);
      pointer-events: none;
    }

    .calendar-day.is-disabled .day-price {
      color: var(--gray-300);
    }

    .calendar-day.no-checkin::before,
    .calendar-day.no-checkout::after {
      content: "";
      position: absolute;
      width: 0;
      height: 0;
      border-top: 10px solid var(--gray-300);
      border-right: 10px solid transparent;
      top: 0;
      left: 0;
      opacity: 0.7;
    }

    .calendar-day.no-checkout::after {
      border-top: 10px solid var(--gray-300);
      border-right: 10px solid transparent;
      top: auto;
      left: auto;
      bottom: 0;
      right: 0;
      transform: rotate(180deg);
    }

    .calendar-day.is-soldout::after {
      content: "×";
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      font-size: 28px;
      color: var(--gray-500);
      opacity: 0.8;
    }

    .calendar-tooltip {
      position: absolute;
      top: -34px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--white);
      border: 1px solid var(--black);
      padding: 4px 8px;
      font-size: 12px;
      font-weight: 600;
      color: var(--black);
      white-space: nowrap;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
      z-index: 2;
    }

    .calendar-tooltip::after {
      content: "";
      position: absolute;
      bottom: -6px;
      left: 50%;
      transform: translateX(-50%);
      border-width: 6px 6px 0;
      border-style: solid;
      border-color: var(--white) transparent transparent transparent;
    }

    .calendar-legend {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      font-size: 12px;
    }

    .calendar-legend.is-hidden {
      display: none;
    }

    .legend-item {
      border: 1px solid var(--gray-300);
      padding: 8px 10px;
      text-align: center;
      position: relative;
      background: linear-gradient(135deg, transparent 45%, rgba(0, 0, 0, 0.05) 45%, rgba(0, 0, 0, 0.05) 55%, transparent 55%);
    }

    .legend-item.is-hidden {
      display: none;
    }

    .calendar-error {
      font-size: 12px;
      color: #b91c1c;
    }

    .guests-section {
      gap: 12px;
    }

    .guest-card {
      border: 1px solid var(--gray-300);
      padding: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .guest-title {
      font-size: 14px;
      font-weight: 600;
    }

    .guest-sub {
      font-size: 12px;
      color: var(--gray-500);
    }

    .counter {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .counter button {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--black);
      background: transparent;
      font-size: 18px;
    }

    .counter span {
      min-width: 20px;
      text-align: center;
      font-weight: 600;
    }

    .dates-modal__footer {
      position: sticky;
      bottom: 0;
      background: var(--white);
      border-top: 1px solid var(--gray-200);
      padding: 14px 0 10px;
      display: grid;
      gap: 10px;
    }

    .dates-modal__summary {
      display: grid;
      gap: 4px;
    }

    .dates-modal__summary-line {
      font-size: 15px;
      font-weight: 600;
    }

    .dates-modal__summary-sub {
      font-size: 12px;
      color: var(--gray-500);
    }

    .dates-modal__cta {
      width: 100%;
      border: none;
      background: var(--black);
      color: var(--white);
      padding: 14px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .dates-modal__cta:disabled {
      opacity: 0.45;
      cursor: not-allowed;
    }

    @media (min-width: 768px) {
      body {
        display: flex;
        justify-content: center;
        background: var(--gray-100);
        padding: 40px 0;
      }

      .mobile-shell {
        border: 1px solid var(--gray-300);
        box-shadow: var(--shadow);
      }
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet" />
</head>
<body>
  <main class="mobile-shell">
    <header class="header">
      <div class="header-inner">
        <button class="icon-button" type="button" id="openMenu" aria-label="<?php echo esc_attr( $strings['menu_label'] ); ?>">≡</button>
        <img
          class="logo"
          src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
          srcset="https://loft1325.com/wp-content/uploads/2024/06/Asset-1-300x108.png 300w, https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png 518w"
          sizes="(max-width: 430px) 180px, 220px"
          alt="Lofts 1325"
        />
        <button class="icon-button language-toggle" type="button" id="headerLanguageToggle" aria-label="<?php echo esc_attr( 'en' === $language ? 'Change language' : 'Changer la langue' ); ?>">
          <span class="language-toggle__label<?php echo 'fr' === $language ? ' is-active' : ''; ?>">FR</span>
          <span>|</span>
          <span class="language-toggle__label<?php echo 'en' === $language ? ' is-active' : ''; ?>">EN</span>
        </button>
      </div>
    </header>

    <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
      <div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle">
        <div class="mobile-menu__header">
          <p class="mobile-menu__title" id="mobileMenuTitle"><?php echo esc_html( $strings['menu_title'] ); ?></p>
          <button class="mobile-menu__close" type="button" id="closeMenu" aria-label="<?php echo esc_attr( $strings['menu_close'] ); ?>">×</button>
        </div>
        <?php
        $mobile_menu_location = has_nav_menu( 'loft1325-mobile-menu' ) ? 'loft1325-mobile-menu' : 'main-menu';

        echo wp_nav_menu(
            array(
                'theme_location' => $mobile_menu_location,
                'container'      => false,
                'menu_class'     => 'mobile-menu__list',
                'fallback_cb'    => false,
                'echo'           => false,
            )
        );
        ?>
      </div>
    </div>

    <section class="hero">
      <h1><?php echo esc_html( $strings['hero_title'] ); ?></h1>
      <p class="hero-tagline"><?php echo wp_kses_post( $strings['hero_tagline'] ); ?></p>
      <div class="search-panel">
        <button class="search-tile" id="openSearch" type="button">
          <span><?php echo esc_html( $strings['dates_tile_label'] ); ?></span>
          <strong id="dateSummary"></strong>
        </button>
        <button class="search-tile" id="openGuests" type="button">
          <span><?php echo esc_html( $strings['guests_tile_label'] ); ?></span>
          <strong id="guestSummary"></strong>
        </button>
      </div>
    </section>

    <?php
    $mobile_homepage = class_exists( 'Loft1325_Mobile_Homepage' ) ? Loft1325_Mobile_Homepage::instance() : null;
    $room_cards      = $mobile_homepage ? $mobile_homepage->get_room_cards() : array();
    $price_prefix    = 'en' === $language ? 'From' : 'À partir de';
    $per_night       = 'en' === $language ? 'per night' : 'par nuit';
    $room_button     = 'en' === $language ? 'Book now' : 'Réserver maintenant';
    $member_label    = 'en' === $language ? 'Rate of the day' : 'Tarif du jour';
    ?>

    <?php if ( ! empty( $room_cards ) ) : ?>
      <section class="room-list">
        <?php foreach ( $room_cards as $room ) : ?>
          <article class="room-card">
            <?php if ( ! empty( $room['image'] ) ) : ?>
              <img
                src="<?php echo esc_url( $room['image'] ); ?>"
                alt="<?php echo esc_attr( $room['title'] ); ?>"
              />
            <?php endif; ?>
            <div class="room-body">
              <div>
                <p class="room-title"><?php echo esc_html( $room['title'] ); ?></p>
                <?php if ( '' !== $room['price'] ) : ?>
                  <p class="room-meta">
                    <?php
                    printf(
                        '%s %s %s · %s',
                        esc_html( $price_prefix ),
                        esc_html( number_format_i18n( (float) $room['price'] ) ),
                        esc_html( $room['currency'] ? $room['currency'] : '$CA' ),
                        esc_html( $per_night )
                    );
                    ?>
                  </p>
                <?php endif; ?>
              </div>
              <p class="room-features"><?php echo esc_html( $room['excerpt'] ); ?></p>
              <div class="rate-block">
                <div class="rate-row">
                  <span><?php echo esc_html( $member_label ); ?></span>
                  <?php if ( '' !== $room['price'] ) : ?>
                    <strong>
                      <?php
                      printf(
                          '%s %s',
                          esc_html( number_format_i18n( (float) $room['price'] ) ),
                          esc_html( $room['currency'] ? $room['currency'] : '$CA' )
                      );
                      ?>
                    </strong>
                  <?php endif; ?>
                </div>
                <a class="primary-button" href="<?php echo esc_url( $room['permalink'] ); ?>"><?php echo esc_html( $room_button ); ?></a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?php
    $restaurant_logos = array(
        array(
            'name'    => "L'oeufrier",
            'file'    => 'oeufrier.svg',
            'setting' => 'restaurant_logo_1',
        ),
        array(
            'name'    => 'Fiesta',
            'file'    => 'fiesta.svg',
            'setting' => 'restaurant_logo_2',
        ),
        array(
            'name'    => 'Toukiparc',
            'file'    => 'toukiparc.svg',
            'setting' => 'restaurant_logo_3',
        ),
        array(
            'name'    => 'Bâton Rouge',
            'file'    => 'baton-rouge.svg',
            'setting' => 'restaurant_logo_4',
        ),
        array(
            'name'    => 'Chocolats Favoris',
            'file'    => 'chocolats-favoris.svg',
            'setting' => 'restaurant_logo_5',
        ),
        array(
            'name'    => 'Restaurant 6',
            'file'    => '',
            'setting' => 'restaurant_logo_6',
        ),
    );
    ?>

    <?php
    $restaurant_logo_items = array();

    foreach ( $restaurant_logos as $restaurant ) {
        $restaurant_logo_id  = (int) get_theme_mod( 'loft1325_mobile_home_' . $restaurant['setting'], 0 );
        $restaurant_logo_url = $restaurant_logo_id ? wp_get_attachment_image_url( $restaurant_logo_id, 'medium' ) : '';

        if ( ! $restaurant_logo_url ) {
            continue;
        }

        $restaurant_logo_items[] = array(
            'name' => $restaurant['name'],
            'url'  => $restaurant_logo_url,
        );
    }
    ?>

    <?php if ( ! empty( $restaurant_logo_items ) ) : ?>
      <section class="restaurant-section">
        <h2><?php echo esc_html( 'en' === $language ? 'Restaurants' : 'Restaurants' ); ?></h2>
        <div class="restaurant-grid">
          <?php foreach ( $restaurant_logo_items as $restaurant ) : ?>
            <div class="restaurant-card">
              <img
                src="<?php echo esc_url( $restaurant['url'] ); ?>"
                alt="<?php echo esc_attr( $restaurant['name'] ); ?>"
                loading="lazy"
              />
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="sticky-bar">
      <div>
        <p class="sticky-price">340,00 $CA</p>
        <p class="sticky-note"><?php echo esc_html( $strings['sticky_note'] ); ?></p>
      </div>
      <button class="primary-button" type="button" id="finalizeButton"><?php echo esc_html( $strings['finalize_cta'] ); ?></button>
    </section>
  </main>

  <div class="modal dates-modal" id="searchModal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="searchTitle">
      <div class="dates-modal__header">
        <span></span>
        <h2 id="searchTitle"><?php echo esc_html( $strings['search_title'] ); ?></h2>
        <button class="icon-button" type="button" id="closeModal" aria-label="<?php echo esc_attr( $strings['close'] ); ?>">×</button>
      </div>

      <section class="dates-modal__section">
        <div class="dates-modal__section-title"><?php echo esc_html( $strings['dates_label'] ); ?></div>
        <div class="dates-modal__month">
          <span></span>
          <span class="dates-modal__month-label" id="calendarMonthLabel"><?php echo esc_html( $strings['month_label'] ); ?></span>
          <button class="dates-modal__chevron" type="button" id="nextMonth" aria-label="<?php echo esc_attr( $strings['next_month'] ); ?>">›</button>
        </div>

        <div class="calendar">
          <div class="calendar-weekdays">
            <?php if ( 'en' === $language ) : ?>
              <span>M</span>
              <span>T</span>
              <span>W</span>
              <span>T</span>
              <span>F</span>
              <span>S</span>
              <span>S</span>
            <?php else : ?>
              <span>L</span>
              <span>M</span>
              <span>M</span>
              <span>J</span>
              <span>V</span>
              <span>S</span>
              <span>D</span>
            <?php endif; ?>
          </div>
          <div class="calendar-grid" id="calendarGrid"></div>
        </div>

        <div class="calendar-legend" id="calendarLegend">
          <span class="legend-item legend-checkin"><?php echo esc_html( $strings['no_checkin'] ); ?></span>
          <span class="legend-item legend-checkout"><?php echo esc_html( $strings['no_checkout'] ); ?></span>
        </div>
        <p class="calendar-error" id="calendarError" hidden><?php echo esc_html( $strings['error_unavailable'] ); ?></p>
      </section>

      <section class="dates-modal__section guests-section">
        <div class="dates-modal__section-title"><?php echo esc_html( $strings['guests_label'] ); ?></div>
        <div class="guest-card">
          <div>
            <p class="guest-title"><?php echo esc_html( $strings['adults_label'] ); ?></p>
            <p class="guest-sub"> </p>
          </div>
          <div class="counter" data-target="adultCount">
            <button type="button" class="minus">-</button>
            <span id="adultCount">2</span>
            <button type="button" class="plus">+</button>
          </div>
        </div>
        <div class="guest-card">
          <div>
            <p class="guest-title"><?php echo esc_html( $strings['children_label'] ); ?></p>
            <p class="guest-sub"> </p>
          </div>
          <div class="counter" data-target="childCount">
            <button type="button" class="minus">-</button>
            <span id="childCount">0</span>
            <button type="button" class="plus">+</button>
          </div>
        </div>
      </section>

      <div class="dates-modal__footer">
        <div class="dates-modal__summary">
          <p class="dates-modal__summary-line" id="priceSummary"></p>
          <p class="dates-modal__summary-sub"><?php echo esc_html( $strings['summary_sub'] ); ?></p>
        </div>
        <button class="dates-modal__cta" type="button" id="modalSearchButton" disabled><?php echo esc_html( $strings['cta'] ); ?></button>
      </div>
    </div>
  </div>

  <?php wp_footer(); ?>
  <script>
    const modal = document.getElementById('searchModal');
    const openSearch = document.getElementById('openSearch');
    const openGuests = document.getElementById('openGuests');
    const closeModal = document.getElementById('closeModal');
    const dateSummary = document.getElementById('dateSummary');
    const guestSummary = document.getElementById('guestSummary');
    const calendarGrid = document.getElementById('calendarGrid');
    const calendarMonthLabel = document.getElementById('calendarMonthLabel');
    const nextMonthButton = document.getElementById('nextMonth');
    const priceSummary = document.getElementById('priceSummary');
    const calendarLegend = document.getElementById('calendarLegend');
    const calendarError = document.getElementById('calendarError');
    const modalSearchButton = document.getElementById('modalSearchButton');
    const headerLanguageToggle = document.getElementById('headerLanguageToggle');
    const openMenu = document.getElementById('openMenu');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMenu = document.getElementById('closeMenu');
    const finalizeButton = document.getElementById('finalizeButton');

    const adultCount = document.getElementById('adultCount');
    const childCount = document.getElementById('childCount');

    const uiCopy = <?php echo wp_json_encode( $strings ); ?>;
    const language = document.documentElement.lang === 'en' ? 'en' : 'fr';
    const searchBaseUrl = <?php echo wp_json_encode( home_url( '/rooms/' ) ); ?>;

    const TOTAL_UNITS = 22;
    const state = {
      selectedStart: null,
      selectedEnd: null,
      activeEnd: null,
      currentMonth: startOfMonth(new Date()),
      ratesCache: new Map(),
      occupancyCache: new Map(),
      restrictionsCache: new Map(),
      tooltipDate: null
    };

    // Configurable pricing engine inputs (single object for revenue rules + guardrails).
    const pricingConfig = {
      baseRate: 240,
      minRate: 180,
      maxRate: 850,
      dayOfWeekMultipliers: {
        0: 1.08,
        1: 1.0,
        2: 1.0,
        3: 1.0,
        4: 1.05,
        5: 1.12,
        6: 1.15
      },
      seasonalMultipliers: [
        { startMonth: 5, endMonth: 8, multiplier: 1.12 },
        { startMonth: 11, endMonth: 11, multiplier: 1.18 }
      ],
      leadTime: {
        long: { threshold: 30, multiplier: 1.04 },
        short: { threshold: 7, highOcc: 1.12, lowOcc: 0.9 }
      },
      occupancyTargets: {
        high: 0.7,
        peak: 0.85
      },
      occupancyMultipliers: {
        high: 1.08,
        peak: 1.18
      },
      specialEventMultipliers: {},
      rounding: {
        strategy: 'psychological',
        endings: [4, 9]
      },
      losDiscount: {
        enabled: false,
        minNights: 3,
        multiplier: 0.95
      }
    };

    function formatDate(dateValue) {
      if (!dateValue) return '';
      return dateValue.toLocaleDateString(language === 'en' ? 'en-CA' : 'fr-CA', {
        month: 'short',
        day: '2-digit'
      });
    }

    function formatCurrency(value) {
      return `${value.toFixed(0)}`;
    }

    function formatTooltip(value) {
      const rounded = formatCurrency(value);
      return language === 'en' ? `CA$${rounded}` : `${rounded} $CA`;
    }

    function setTooltipDate(iso) {
      state.tooltipDate = iso;
    }

    function clearTooltip() {
      state.tooltipDate = null;
    }

    function showCalendarError() {
      if (calendarError) {
        calendarError.removeAttribute('hidden');
      }
    }

    function hideCalendarError() {
      if (calendarError) {
        calendarError.setAttribute('hidden', 'hidden');
      }
    }

    function updateLegendVisibility(restrictions, monthStart, monthEnd) {
      if (!calendarLegend) return;
      let hasCheckIn = false;
      let hasCheckOut = false;
      const cursor = new Date(monthStart);
      while (cursor <= monthEnd) {
        const restriction = restrictions[toISODate(cursor)];
        if (restriction?.noCheckIn) hasCheckIn = true;
        if (restriction?.noCheckOut) hasCheckOut = true;
        cursor.setDate(cursor.getDate() + 1);
      }
      calendarLegend.classList.toggle('is-hidden', !(hasCheckIn || hasCheckOut));
      const items = calendarLegend.querySelectorAll('.legend-item');
      if (items.length >= 2) {
        items[0].classList.toggle('is-hidden', !hasCheckIn);
        items[1].classList.toggle('is-hidden', !hasCheckOut);
      }
    }


    function getLanguageUrl(targetLanguage) {
      const switcherLinks = document.querySelectorAll('#trp-floater-ls-language-list a[href], .trp-language-switcher-container a[href]');
      for (const link of switcherLinks) {
        const href = link.getAttribute('href');
        if (!href || href === '#') {
          continue;
        }

        try {
          const url = new URL(href, window.location.origin);
          const pathSegments = url.pathname.replace(/^\/+/, '').split('/');
          const firstSegment = (pathSegments[0] || '').toLowerCase();

          if (targetLanguage === 'en' && firstSegment === 'en') {
            return url.toString();
          }

          if (targetLanguage === 'fr' && firstSegment !== 'en') {
            return url.toString();
          }
        } catch (error) {
          continue;
        }
      }

      const fallbackUrl = new URL(window.location.href);
      const segments = fallbackUrl.pathname.replace(/^\/+/, '').split('/').filter(Boolean);

      if (targetLanguage === 'en') {
        if (segments[0] !== 'en') {
          segments.unshift('en');
        }
      } else if (segments[0] === 'en') {
        segments.shift();
      }

      fallbackUrl.pathname = `/${segments.join('/')}${segments.length ? '/' : ''}`;

      return fallbackUrl.toString();
    }

    function toISODate(date) {
      return date.toISOString().split('T')[0];
    }

    function startOfMonth(date) {
      return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    function endOfMonth(date) {
      return new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }

    function addMonths(date, amount) {
      return new Date(date.getFullYear(), date.getMonth() + amount, 1);
    }

    function isSameDay(a, b) {
      return a && b && a.toDateString() === b.toDateString();
    }

    function isBetween(date, start, end) {
      return start && end && date > start && date < end;
    }

    function daysBetween(start, end) {
      const days = [];
      const current = new Date(start);
      while (current <= end) {
        days.push(new Date(current));
        current.setDate(current.getDate() + 1);
      }
      return days;
    }

    function updateSummary() {
      if (!state.selectedStart && !state.selectedEnd) {
        dateSummary.textContent = uiCopy.date_range_placeholder;
      } else {
        const arrival = state.selectedStart ? formatDate(state.selectedStart) : uiCopy.date_placeholder;
        const depart = state.selectedEnd ? formatDate(state.selectedEnd) : uiCopy.date_placeholder;
        dateSummary.textContent = `${arrival} · ${depart}`;
      }
      const adultLabel = Number(adultCount.textContent) > 1 ? uiCopy.adult_plural : uiCopy.adult_singular;
      const childLabel = Number(childCount.textContent) > 1 ? uiCopy.child_plural : uiCopy.child_singular;
      guestSummary.textContent = `${adultCount.textContent} ${adultLabel} · ${childCount.textContent} ${childLabel}`;
    }

    function openModal() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      preloadMonths();
      renderCalendar();
    }

    function closeModalView() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
    }

    function openMenuPanel() {
      if (!mobileMenu) return;
      mobileMenu.classList.add('is-open');
      mobileMenu.setAttribute('aria-hidden', 'false');
    }

    function closeMenuPanel() {
      if (!mobileMenu) return;
      mobileMenu.classList.remove('is-open');
      mobileMenu.setAttribute('aria-hidden', 'true');
    }

    function buildSearchUrl() {
      const url = new URL(searchBaseUrl, window.location.origin);
      const guests = Math.max(1, Number(adultCount.textContent) + Number(childCount.textContent));
      if (state.selectedStart) {
        url.searchParams.set('nd_booking_archive_form_date_range_from', toISODate(state.selectedStart));
      }
      if (state.selectedEnd) {
        url.searchParams.set('nd_booking_archive_form_date_range_to', toISODate(state.selectedEnd));
      }
      url.searchParams.set('nd_booking_archive_form_guests', guests.toString());
      return url.toString();
    }

    function handleSearchRedirect() {
      if (!state.selectedStart || !state.selectedEnd) {
        openModal();
        return;
      }
      window.location.href = buildSearchUrl();
    }

    function getMonthLabel(date) {
      return date.toLocaleDateString(language === 'en' ? 'en-CA' : 'fr-CA', {
        month: 'long',
        year: 'numeric'
      }).toUpperCase();
    }

    function getMonthKey(start, end, guests) {
      return `${toISODate(start)}_${toISODate(end)}_${guests}`;
    }

    function mockOccupancyForRange(start, end) {
      const data = {};
      const cursor = new Date(start);
      while (cursor <= end) {
        const day = cursor.getDate();
        const monthFactor = (cursor.getMonth() + 3) % 6;
        const occupancy = Math.min(TOTAL_UNITS, 10 + (day % 9) + monthFactor);
        data[toISODate(cursor)] = occupancy;
        cursor.setDate(cursor.getDate() + 1);
      }
      return data;
    }

    function mockRestrictionsForRange(start, end) {
      const data = {};
      const cursor = new Date(start);
      while (cursor <= end) {
        const iso = toISODate(cursor);
        data[iso] = {
          noCheckIn: cursor.getDay() === 2,
          noCheckOut: cursor.getDay() === 5
        };
        cursor.setDate(cursor.getDate() + 1);
      }
      return data;
    }

    function getSeasonalMultiplier(date) {
      const month = date.getMonth();
      const seasonal = pricingConfig.seasonalMultipliers.find((entry) => {
        if (entry.startMonth <= entry.endMonth) {
          return month >= entry.startMonth && month <= entry.endMonth;
        }
        return month >= entry.startMonth || month <= entry.endMonth;
      });
      return seasonal ? seasonal.multiplier : 1;
    }

    function applyPsychologicalRounding(value) {
      const base = Math.floor(value / 10) * 10;
      const candidates = pricingConfig.rounding.endings.map((ending) => base + ending);
      candidates.push(base + 10 + pricingConfig.rounding.endings[0]);
      return candidates.reduce((closest, candidate) => {
        return Math.abs(candidate - value) < Math.abs(closest - value) ? candidate : closest;
      }, candidates[0]);
    }

    function nightlyRate(date, options) {
      const dowMultiplier = pricingConfig.dayOfWeekMultipliers[date.getDay()] || 1;
      const seasonalMultiplier = getSeasonalMultiplier(date);
      let rate = pricingConfig.baseRate * dowMultiplier * seasonalMultiplier;

      if (options.specialEventMultiplier) {
        rate *= options.specialEventMultiplier;
      }

      if (options.leadTimeDays > pricingConfig.leadTime.long.threshold) {
        rate *= pricingConfig.leadTime.long.multiplier;
      } else if (options.leadTimeDays < pricingConfig.leadTime.short.threshold) {
        if (options.occupancyPercent >= pricingConfig.occupancyTargets.high) {
          rate *= pricingConfig.leadTime.short.highOcc;
        } else if (options.occupancyPercent < 0.35) {
          rate *= pricingConfig.leadTime.short.lowOcc;
        }
      }

      if (options.occupancyPercent >= pricingConfig.occupancyTargets.peak) {
        rate *= pricingConfig.occupancyMultipliers.peak;
      } else if (options.occupancyPercent >= pricingConfig.occupancyTargets.high) {
        rate *= pricingConfig.occupancyMultipliers.high;
      }

      if (options.pickupVelocity && options.pickupVelocity > 1) {
        rate *= Math.min(1.05 + (options.pickupVelocity - 1) * 0.02, 1.12);
      }

      rate = Math.max(pricingConfig.minRate, Math.min(pricingConfig.maxRate, rate));
      rate = applyPsychologicalRounding(rate);
      return Math.round(rate);
    }

    async function getDailyRates(monthStart, monthEnd, guestCount, promoCode, occupancyByDate) {
      const key = getMonthKey(monthStart, monthEnd, guestCount);
      if (state.ratesCache.has(key)) {
        return state.ratesCache.get(key);
      }

      const data = {};
      const cursor = new Date(monthStart);
      const today = new Date();
      const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
      while (cursor <= monthEnd) {
        const iso = toISODate(cursor);
        const occupiedUnits = occupancyByDate[iso] ?? 0;
        const occupancyPercent = TOTAL_UNITS ? occupiedUnits / TOTAL_UNITS : 0;
        const leadTimeDays = Math.max(0, Math.ceil((cursor - todayMidnight) / 86400000));
        const specialEventMultiplier = pricingConfig.specialEventMultipliers[iso] || null;
        data[iso] = nightlyRate(cursor, {
          leadTimeDays,
          occupancyPercent,
          pickupVelocity: null,
          specialEventMultiplier
        });
        cursor.setDate(cursor.getDate() + 1);
      }
      state.ratesCache.set(key, data);
      return data;
    }

    async function getOccupancyByDateRange(startDate, endDate) {
      const key = `${toISODate(startDate)}_${toISODate(endDate)}`;
      if (state.occupancyCache.has(key)) {
        return state.occupancyCache.get(key);
      }

      // TODO: Replace with Butterfly adapter call.
      // Example: const response = await fetch(`/wp-json/loft-booking/v1/occupancy?start=${toISODate(startDate)}&end=${toISODate(endDate)}`);
      // const data = await response.json();
      const data = mockOccupancyForRange(startDate, endDate);
      state.occupancyCache.set(key, data);
      return data;
    }

    async function getRestrictionsByDateRange(startDate, endDate) {
      const key = `${toISODate(startDate)}_${toISODate(endDate)}`;
      if (state.restrictionsCache.has(key)) {
        return state.restrictionsCache.get(key);
      }

      // TODO: Replace with restrictions endpoint.
      const data = mockRestrictionsForRange(startDate, endDate);
      state.restrictionsCache.set(key, data);
      return data;
    }

    async function preloadMonths() {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const nextStart = addMonths(monthStart, 1);
      const nextEnd = endOfMonth(nextStart);
      const guests = Number(adultCount.textContent) + Number(childCount.textContent);

      const [occupancy, restrictions] = await Promise.all([
        getOccupancyByDateRange(monthStart, nextEnd),
        getRestrictionsByDateRange(monthStart, nextEnd)
      ]);
      await Promise.all([
        getDailyRates(monthStart, monthEnd, guests, null, occupancy),
        getDailyRates(nextStart, nextEnd, guests, null, occupancy)
      ]);
    }

    async function renderCalendar() {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const guests = Number(adultCount.textContent) + Number(childCount.textContent);

      const [occupancy, restrictions] = await Promise.all([
        getOccupancyByDateRange(monthStart, monthEnd),
        getRestrictionsByDateRange(monthStart, monthEnd)
      ]);
      const rates = await getDailyRates(monthStart, monthEnd, guests, null, occupancy);

      calendarMonthLabel.textContent = getMonthLabel(monthStart);
      calendarGrid.innerHTML = '';
      hideCalendarError();
      if (state.tooltipDate) {
        const tooltipDate = new Date(state.tooltipDate);
        if (tooltipDate < monthStart || tooltipDate > monthEnd) {
          clearTooltip();
        }
      }

      const firstDay = monthStart.getDay();
      const mondayStartOffset = (firstDay + 6) % 7;
      for (let i = 0; i < mondayStartOffset; i += 1) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-day is-empty';
        calendarGrid.appendChild(emptyCell);
      }

      const today = new Date();
      const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());

      for (let day = 1; day <= monthEnd.getDate(); day += 1) {
        const date = new Date(monthStart.getFullYear(), monthStart.getMonth(), day);
        const iso = toISODate(date);
        const price = rates[iso];
        const occupiedUnits = occupancy[iso] ?? 0;
        const restriction = restrictions[iso] || { noCheckIn: false, noCheckOut: false };
        const soldOut = occupiedUnits >= TOTAL_UNITS;
        const isPast = date < todayMidnight;
        const isDisabled = soldOut || isPast;
        const isActiveEnd =
          (state.activeEnd === 'start' && isSameDay(date, state.selectedStart)) ||
          (state.activeEnd === 'end' && isSameDay(date, state.selectedEnd));

        const cell = document.createElement('button');
        cell.type = 'button';
        cell.className = 'calendar-day';
        cell.dataset.date = iso;
        cell.dataset.price = price || '';

        if (restriction.noCheckIn) {
          cell.classList.add('no-checkin');
        }
        if (restriction.noCheckOut) {
          cell.classList.add('no-checkout');
        }
        if (soldOut) {
          cell.classList.add('is-soldout');
        }
        if (isDisabled) {
          cell.classList.add('is-disabled');
        }
        cell.disabled = isDisabled;
        if (isSameDay(date, state.selectedStart)) {
          cell.classList.add('is-start');
        }
        if (isSameDay(date, state.selectedEnd)) {
          cell.classList.add('is-end');
        }
        if (isBetween(date, state.selectedStart, state.selectedEnd)) {
          cell.classList.add('is-range');
        }
        if (isActiveEnd) {
          cell.classList.add('is-active-end');
        }

        const dayNumber = document.createElement('span');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;

        const dayPrice = document.createElement('span');
        dayPrice.className = 'day-price';
        if (!soldOut && price) {
          dayPrice.textContent = price.toFixed(0);
        } else {
          dayPrice.textContent = '';
        }

        cell.append(dayNumber, dayPrice);

        if (state.tooltipDate === iso && price) {
          const tooltip = document.createElement('div');
          tooltip.className = 'calendar-tooltip';
          tooltip.textContent = formatTooltip(price);
          cell.appendChild(tooltip);
        }

        if (!isDisabled) {
          cell.addEventListener('click', () => {
            setTooltipDate(iso);
            handleDateClick(date, restriction);
          });
        }

        calendarGrid.appendChild(cell);
      }

      updatePriceSummary(rates);
      updateLegendVisibility(restrictions, monthStart, monthEnd);
    }

    function updatePriceSummary(rates) {
      let total = 0;
      let nights = 0;
      if (state.selectedStart && state.selectedEnd) {
        const nightsArray = daysBetween(state.selectedStart, new Date(state.selectedEnd.getTime() - 86400000));
        nights = nightsArray.length;
        total = nightsArray.reduce((sum, date) => {
          const price = rates[toISODate(date)] || 0;
          return sum + price;
        }, 0);
        if (pricingConfig.losDiscount.enabled && nights >= pricingConfig.losDiscount.minNights) {
          // Enable only after validating conversion impact; defaults OFF.
          total = Math.round(total * pricingConfig.losDiscount.multiplier);
        }
      } else {
        const values = Object.values(rates).filter(Boolean);
        total = values.length ? Math.min(...values) : 0;
        nights = 1;
      }
      const formatted = formatCurrency(total || 0);
      if (nights === 1) {
        priceSummary.textContent = uiCopy.summary_template_empty.replace('%1$s', formatted).replace('%2$s', nights);
      } else {
        priceSummary.textContent = uiCopy.summary_template.replace('%1$s', formatted).replace('%2$s', nights);
      }
      modalSearchButton.disabled = !(state.selectedStart && state.selectedEnd);
    }

    function isRangeAvailable(start, end, occupancy, restrictions) {
      const dates = daysBetween(start, end);
      return dates.every((date, index) => {
        const iso = toISODate(date);
        const occupiedUnits = occupancy[iso] ?? 0;
        const restriction = restrictions[iso] || { noCheckIn: false, noCheckOut: false };
        if (occupiedUnits >= TOTAL_UNITS) return false;
        if (index === 0 && restriction.noCheckIn) return false;
        if (index === dates.length - 1 && restriction.noCheckOut) return false;
        return true;
      });
    }

    async function handleDateClick(date, restriction) {
      const monthStart = state.currentMonth;
      const monthEnd = endOfMonth(monthStart);
      const occupancy = await getOccupancyByDateRange(monthStart, monthEnd);
      const restrictions = await getRestrictionsByDateRange(monthStart, monthEnd);

      hideCalendarError();

      if (!state.selectedStart || (state.selectedStart && state.selectedEnd)) {
        if (restriction.noCheckIn) return;
        state.selectedStart = date;
        state.selectedEnd = null;
        state.activeEnd = 'start';
      } else if (state.selectedStart && !state.selectedEnd) {
        let start = state.selectedStart;
        let end = date;
        if (end.getTime() === start.getTime()) {
          state.activeEnd = 'start';
          renderCalendar();
          return;
        }
        if (end < start) {
          const temp = start;
          start = end;
          end = temp;
        }

        const startRestriction = restrictions[toISODate(start)] || { noCheckIn: false };
        const endRestriction = restrictions[toISODate(end)] || { noCheckOut: false };
        if (startRestriction.noCheckIn || endRestriction.noCheckOut) {
          showCalendarError();
          renderCalendar();
          return;
        }

        const rangeOk = isRangeAvailable(start, end, occupancy, restrictions);
        if (!rangeOk) {
          showCalendarError();
          renderCalendar();
          return;
        }

        state.selectedStart = start;
        state.selectedEnd = end;
        state.activeEnd = 'end';
      }
      updateSummary();
      renderCalendar();
    }

    openSearch.addEventListener('click', openModal);
    openGuests.addEventListener('click', openModal);
    closeModal.addEventListener('click', closeModalView);

    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModalView();
      }
    });


    if (headerLanguageToggle) {
      headerLanguageToggle.addEventListener('click', () => {
        const targetLanguage = language === 'en' ? 'fr' : 'en';
        window.location.href = getLanguageUrl(targetLanguage);
      });
    }

    if (openMenu) {
      openMenu.addEventListener('click', openMenuPanel);
    }

    if (closeMenu) {
      closeMenu.addEventListener('click', closeMenuPanel);
    }

    if (mobileMenu) {
      mobileMenu.addEventListener('click', (event) => {
        if (event.target === mobileMenu) {
          closeMenuPanel();
        }
      });

      mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenuPanel);
      });
    }

    nextMonthButton.addEventListener('click', () => {
      state.currentMonth = addMonths(state.currentMonth, 1);
      preloadMonths();
      renderCalendar();
    });

    document.querySelectorAll('.counter').forEach((counter) => {
      const minus = counter.querySelector('.minus');
      const plus = counter.querySelector('.plus');
      const target = document.getElementById(counter.dataset.target);

      minus.addEventListener('click', () => {
        const value = Math.max(0, Number(target.textContent) - 1);
        target.textContent = value;
        preloadMonths();
        renderCalendar();
        updateSummary();
      });

      plus.addEventListener('click', () => {
        target.textContent = Number(target.textContent) + 1;
        preloadMonths();
        renderCalendar();
        updateSummary();
      });
    });

    if (modalSearchButton) {
      modalSearchButton.addEventListener('click', handleSearchRedirect);
    }

    if (finalizeButton) {
      finalizeButton.addEventListener('click', handleSearchRedirect);
    }

    updateSummary();
  </script>
</body>
</html>
