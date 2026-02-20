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
    'hero_tagline'           => $language === 'en' ? 'VIRTUAL HOTEL<br />EXPERIENCE' : 'EXPÉRIENCE HÔTELIÈRE<br />100 % VIRTUELLE',
    'hero_copy'              => $language === 'en' ? 'Enjoy the comfort of home with a hotel experience, and manage everything from your phone.' : "Le confort d'une maison avec l'expérience hôtelière, gérez tout depuis votre mobile.",
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

get_header( 'mobile' ); ?>

<div class="mobile-shell">

  <main class="main-content">

    <section class="hero-section">
      <h1 class="hero-title"><?php echo esc_html( $strings["hero_title"] ); ?></h1>
      <p class="hero-tagline"><?php echo $strings["hero_tagline"]; ?></p>
      <p class="hero-copy"><?php echo esc_html( $strings["hero_copy"] ); ?></p>
    </section>

    <section class="search-section">
      <h2 class="search-title"><?php echo esc_html( $strings["search_title"] ); ?></h2>
      <div class="search-field">
        <label for="dates-input"><?php echo esc_html( $strings["dates_tile_label"] ); ?></label>
        <button id="dates-input"><span><?php echo esc_html( $strings["date_range_placeholder"] ); ?></span> <span class="icon-calendar"></span></button>
      </div>
      <div class="search-field">
        <label for="guests-input"><?php echo esc_html( $strings["guests_tile_label"] ); ?></label>
        <button id="guests-input"><span>2 <?php echo esc_html( $strings["adult_plural"] ); ?> · 0 <?php echo esc_html( $strings["child_plural"] ); ?></span> <span class="icon-user"></span></button>
      </div>
    </section>

    <?php
    $mobile_homepage = class_exists( 'Loft1325_Mobile_Homepage' ) ? Loft1325_Mobile_Homepage::instance() : null;
    $room_cards      = $mobile_homepage ? $mobile_homepage->get_room_cards() : array();
    $price_prefix    = 'en' === $language ? 'From' : 'À partir de';
    $per_night       = 'en' === $language ? 'per night' : 'par nuit';
    $room_button     = 'en' === $language ? 'BOOK NOW' : 'RÉSERVER MAINTENANT';
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
            'name'    => "Toukiparc",
            'file'    => 'toukiparc.svg',
            'setting' => 'restaurant_logo_2',
        ),
        array(
            'name'    => "Chocolats Favoris",
            'file'    => 'chocolats-favoris.svg',
            'setting' => 'restaurant_logo_3',
        ),
        array(
            'name'    => "Bâton Rouge",
            'file'    => 'baton-rouge.svg',
            'setting' => 'restaurant_logo_4',
        ),
        array(
            'name'    => "La Fiesta",
            'file'    => 'fiesta.svg',
            'setting' => 'restaurant_logo_5',
        ),
    );

    $has_logos = false;
    foreach ( $restaurant_logos as $key => $logo ) {
        $logo_url = get_theme_mod( 'loft1325_mobile_home_' . $logo['setting'], '' );
        if ( ! empty( $logo_url ) ) {
            $has_logos = true;
            break;
        }
    }

    if ( $has_logos ) : ?>
      <section class="restaurant-list">
        <h2>Restaurants</h2>
        <div class="restaurant-grid">
          <?php foreach ( $restaurant_logos as $logo ) : ?>
            <?php
            $logo_url = get_theme_mod( 'loft1325_mobile_home_' . $logo['setting'], '' );
            if ( empty( $logo_url ) ) {
                continue;
            }
            ?>
            <div class="restaurant-logo">
              <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo['name'] ); ?>" />
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </main><!-- .main-content -->

</div><!-- .mobile-shell -->

<?php get_footer( 'mobile' ); ?>
); ?>
'mobile
' ); ?>
' ); ?>
'mobile' ); ?>
