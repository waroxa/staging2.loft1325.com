<?php
/**
 * Mobile-only front page template.
 *
 * @package Loft1325\MobileHomepage
 */

defined( 'ABSPATH' ) || exit;

$plugin = Loft1325_Mobile_Homepage::instance();

$hero_background_id = (int) get_theme_mod( 'loft1325_mobile_home_hero_background', 0 );
$hero_background    = $hero_background_id ? wp_get_attachment_image_url( $hero_background_id, 'full' ) : '';
$rooms_archive      = get_post_type_archive_link( 'nd_booking_cpt_1' );
$language           = $plugin->get_current_language();

$arrival_label      = $plugin->localize_label( 'Arrivée', 'Arrival' );
$departure_label    = $plugin->localize_label( 'Départ', 'Departure' );
$date_placeholder   = $plugin->localize_label( 'Sélectionner les dates', 'Select dates' );
$guest_singular     = $plugin->localize_label( 'invité', 'guest' );
$guest_plural       = $plugin->localize_label( 'invités', 'guests' );
$night_singular     = $plugin->localize_label( 'nuit', 'night' );
$night_plural       = $plugin->localize_label( 'nuits', 'nights' );
$features_heading   = $plugin->localize_label( 'Avantages principaux', 'Key benefits' );
$empty_rooms_text   = $plugin->localize_label( 'Aucun loft n’est actuellement disponible. Ajoutez vos chambres ND Booking pour alimenter cette section.', 'No lofts are currently available. Add your ND Booking rooms to populate this section.' );
$price_label        = ( 'en' === $language ) ? 'From %1$s%2$s' : 'À partir de %1$s%2$s';
$rating_label       = $plugin->localize_label( 'Note %s sur 5', 'Rating %s out of 5' );
$per_night_label    = $plugin->localize_label( 'par nuit', 'per night' );
$dates_label        = $plugin->get_string( 'search_date_label' );
$guests_label       = $plugin->get_string( 'search_guests_label' );
$default_total_guests = 1;
$language_attr      = ( 'en' === $language ) ? 'en' : 'fr';
$search_action      = $plugin->get_mobile_search_action_url();
$check_in_value     = '';
$check_out_value    = '';

$plugin->enqueue_search_dependencies();

if ( ! $rooms_archive ) {
    $rooms_archive = home_url( '/' );
}

get_header();
?>

<style id="loft1325-mobile-home-inline-style">
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__rooms,
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta {
    background: linear-gradient(180deg, #f7f7f9 0%, #eef3f6 100%);
    padding: 2.75rem 1.5rem 3.25rem;
    color: #0b0b0b;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta {
    padding: 3rem 1.5rem 3.65rem;
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__section-heading,
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-list,
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-inner,
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-footer {
    max-width: 780px;
    margin: 0 auto;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__section-heading {
    display: grid;
    gap: 0.75rem;
    margin-bottom: 1.75rem;
    text-align: left;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__section-title {
    font-size: 1.35rem;
    margin: 0;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.01em;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__section-description {
    font-size: 0.94rem;
    color: rgba(11, 11, 11, 0.7);
    margin: 0;
    line-height: 1.65;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-list {
    display: grid;
    gap: 1.5rem;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-card {
    background: #ffffff;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 18px 32px rgba(15, 27, 45, 0.08);
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(118, 177, 196, 0.28);
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-media img {
    width: 100%;
    height: 210px;
    object-fit: cover;
    display: block;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-body {
    padding: 1.5rem;
    display: grid;
    gap: 1rem;
    color: #0b0b0b;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-badge {
    align-self: flex-start;
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    background: #FDB913;
    color: #030213;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.02em;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-title {
    margin: 0;
    font-size: 1.12rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-title a {
    color: #0f172a;
    text-decoration: none;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-title a:hover,
body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-title a:focus {
    color: #0b1220;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-text {
    margin: 0;
    font-size: 0.92rem;
    line-height: 1.65;
    color: rgba(11, 11, 11, 0.7);
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-price {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: #0f172a;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-price-unit {
    margin-left: 0.25rem;
    font-size: 0.82rem;
    color: rgba(11, 11, 11, 0.65);
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__room-footer {
    margin-top: 2.35rem;
    display: flex;
    justify-content: center;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-inner {
    position: relative;
    z-index: 1;
    background-color: #ffffff;
    border-radius: 26px;
    padding: 2.4rem 1.65rem;
    display: grid;
    gap: 1.35rem;
    text-align: center;
    border: 1px solid rgba(118, 177, 196, 0.28);
    box-shadow: 0 22px 38px rgba(15, 27, 45, 0.08);
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-title {
    margin: 0;
    font-size: clamp(1.45rem, 5.8vw, 1.85rem);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: 0.01em;
    color: #0f172a;
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-text {
    margin: 0;
    font-size: clamp(0.95rem, 4.2vw, 1.06rem);
    line-height: 1.7;
    color: rgba(11, 11, 11, 0.7);
}

body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-actions {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 0.5rem;
    justify-content: center;
}

@media (max-width: 420px) {
    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__search-card {
        padding: 0.9rem;
    }
}

@media (min-width: 600px) {
    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta-actions {
        flex-direction: row;
        justify-content: center;
    }
}

@media (min-width: 768px) {
    body.loft1325-mobile-home-active #loft1325-mobile-homepage {
        max-width: 480px;
        margin: 0 auto;
    }

    body.loft1325-mobile-home-active .loft1325-mobile-home__wrapper {
        background: #f7f7f9;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__cta,
    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__rooms,
    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__features,
    body.loft1325-mobile-home-active #loft1325-mobile-homepage .loft1325-mobile-home__hero {
        max-width: 520px;
        width: 100%;
    }
}
</style>

<div class="loft1325-mobile-home__wrapper">
    <header class="loft1325-mobile-home__topbar">
        <div class="loft1325-mobile-home__logo">
            <img
                class="loft1325-mobile-home__logo-img"
                src="<?php echo esc_url( 'https://staging2.loft1325.com/wp-content/uploads/2024/06/Asset-1.png' ); ?>"
                alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                loading="lazy"
            />
        </div>
        <button
            class="loft1325-mobile-home__nav-toggle"
            type="button"
            aria-label="<?php echo esc_attr( $plugin->localize_label( 'Ouvrir le menu', 'Open menu' ) ); ?>"
            aria-expanded="false"
            aria-controls="loft1325-mobile-nav"
            data-loft1325-mobile-nav-toggle
        >
            <span class="loft1325-mobile-home__nav-toggle-bar"></span>
            <span class="loft1325-mobile-home__nav-toggle-bar"></span>
            <span class="loft1325-mobile-home__nav-toggle-bar"></span>
        </button>
    </header>

    <div class="loft1325-mobile-home__nav-overlay" data-loft1325-mobile-nav-overlay hidden></div>
    <nav id="loft1325-mobile-nav" class="loft1325-mobile-home__nav" aria-hidden="true" hidden>
        <div class="loft1325-mobile-home__nav-inner">
            <?php
            $menu = wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'echo'           => false,
                    'fallback_cb'    => false,
                )
            );

            if ( ! $menu ) {
                $menu = wp_page_menu(
                    array(
                        'echo'      => false,
                        'menu_class'=> 'menu',
                    )
                );
            }

            echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    </nav>

    <main id="loft1325-mobile-homepage" class="loft1325-mobile-home">
        <section class="loft1325-mobile-home__hero" style="<?php echo $hero_background ? 'background-image: url(' . esc_url( $hero_background ) . ');' : ''; ?>">
            <div class="loft1325-mobile-home__hero-overlay"></div>
            <div class="loft1325-mobile-home__hero-body">
                <div class="loft1325-mobile-home__hero-content">
                    <span class="loft1325-mobile-home__hero-pill"><?php echo esc_html( $plugin->get_string( 'hero_tagline' ) ); ?></span>
                    <?php
                    $hero_title = $plugin->get_string( 'hero_title' );
                    if ( function_exists( 'mb_convert_case' ) ) {
                        $hero_title = mb_convert_case( $hero_title, MB_CASE_TITLE, 'UTF-8' );
                    } else {
                        $hero_title = ucwords( strtolower( $hero_title ) );
                    }
                    $hero_title = preg_replace( '/\s*100\s*%/u', '<br>100 %', $hero_title );
                    ?>
                    <h1 class="loft1325-mobile-home__hero-title" style="font-size: 1.77rem !important;
        color: #ffffff !important;line-height: 1.25 !important;
    font-weight: 700 !important;"><?php echo wp_kses( $hero_title, array( 'br' => array() ) ); ?></h1>
                    <p class="loft1325-mobile-home__hero-text"><?php echo esc_html( $plugin->get_string( 'hero_description' ) ); ?></p>
                </div>

                <div
                    class="loft1325-mobile-home__search-card"
                    id="loft1325-mobile-home-search"
                    data-date-label="<?php echo esc_attr( $plugin->get_string( 'search_date_label' ) ); ?>"
                    data-arrival-label="<?php echo esc_attr( $arrival_label ); ?>"
                    data-departure-label="<?php echo esc_attr( $departure_label ); ?>"
                    data-guests-label="<?php echo esc_attr( $plugin->get_string( 'search_guests_label' ) ); ?>"
                    data-submit-label="<?php echo esc_attr( $plugin->get_string( 'search_submit_label' ) ); ?>"
                    data-date-placeholder="<?php echo esc_attr( $date_placeholder ); ?>"
                    data-guests-singular="<?php echo esc_attr( $guest_singular ); ?>"
                    data-guests-plural="<?php echo esc_attr( $guest_plural ); ?>"
                    data-nights-singular="<?php echo esc_attr( $night_singular ); ?>"
                    data-nights-plural="<?php echo esc_attr( $night_plural ); ?>"
                >
                    <div class="loft-search-card loft-search-card--stacked">
                        <div class="loft-search-card__body loft1325-mobile-home__search-form">
                            <form id="nd_booking_search_cpt_1_form_sidebar" class="loft-search-toolbar__form loft-search-toolbar__form--card" action="<?php echo esc_url( $search_action ); ?>" method="get" data-language="<?php echo esc_attr( $language_attr ); ?>">
                                <div id="nd_booking_search_main_bg" class="loft-search-toolbar nd_booking_search_form">
                                    <div class="loft-booking-card">
                                        <div class="loft-booking-card__field loft-booking-card__field--location">
                                            <span class="loft-booking-card__field-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                                    <path d="M20 10c0 5-8 12-8 12s-8-7-8-12a8 8 0 1 1 16 0z"></path>
                                                    <circle cx="12" cy="10" r="3"></circle>
                                                </svg>
                                            </span>
                                            <div class="loft-booking-card__field-body">
                                                <label class="loft-search-toolbar__label"><?php echo esc_html( $plugin->get_string( 'search_location_label' ) ); ?></label>
                                                <span class="loft-booking-card__value"><?php echo esc_html( $plugin->get_string( 'search_location_value' ) ); ?></span>
                                            </div>
                                        </div>

                                        <div class="loft-booking-card__field loft-booking-card__field--date-range" data-date-field>
                                            <span class="loft-booking-card__field-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                                    <rect x="4" y="5" width="16" height="16" rx="2"></rect>
                                                    <line x1="16" y1="3" x2="16" y2="7"></line>
                                                    <line x1="8" y1="3" x2="8" y2="7"></line>
                                                    <line x1="4" y1="11" x2="20" y2="11"></line>
                                                </svg>
                                            </span>
                                            <div class="loft-booking-card__field-body">
                                                <label class="loft-search-toolbar__label" for="loft_booking_date_range"><?php echo esc_html( $dates_label ); ?></label>
                                                <div class="loft-booking-card__date-input">
                                                    <input
                                                        type="text"
                                                        id="loft_booking_date_range"
                                                        class="loft-booking-card__input loft-booking-card__input--date"
                                                        placeholder="<?php echo esc_attr( $date_placeholder ); ?>"
                                                        autocomplete="off"
                                                        readonly
                                                        aria-label="<?php echo esc_attr( $dates_label ); ?>"
                                                    />
                                                    <button type="button" class="loft-booking-card__clear" aria-label="<?php echo esc_attr( $plugin->localize_label( 'Effacer la plage de dates', 'Clear date range' ) ); ?>" data-date-clear>&times;</button>
                                                </div>
                                            </div>
                                            <input type="text" id="nd_booking_archive_form_date_range_from" name="nd_booking_archive_form_date_range_from" class="loft-booking-card__hidden-input loft-search-toolbar__input" value="<?php echo esc_attr( $check_in_value ); ?>" autocomplete="off" readonly />
                                            <input type="text" id="nd_booking_archive_form_date_range_to" name="nd_booking_archive_form_date_range_to" class="loft-booking-card__hidden-input loft-search-toolbar__input" value="<?php echo esc_attr( $check_out_value ); ?>" autocomplete="off" readonly />
                                        </div>

                                        <div class="loft-booking-card__field loft-booking-card__field--guests">
                                            <span class="loft-booking-card__field-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                                    <path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0z"></path>
                                                    <path d="M4 20a8 8 0 0 1 16 0"></path>
                                                </svg>
                                            </span>
                                            <div class="loft-booking-card__field-body">
                                                <label class="loft-search-toolbar__label" for="loft_booking_guests"><?php echo esc_html( $guests_label ); ?></label>
                                                <div class="loft-search-toolbar__control loft-search-toolbar__control--guests loft-search-toolbar__group loft-search-toolbar__guests" data-guest-group="total">
                                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="down" aria-label="<?php echo esc_attr( $plugin->localize_label( 'Diminuer le nombre d’invités', 'Decrease guest count' ) ); ?>">−</button>
                                                    <span class="loft-search-toolbar__guests-value" id="loft_booking_guests_value">
                                                        <?php echo esc_html( $default_total_guests . ' ' . ( 1 === $default_total_guests ? $guest_singular : $guest_plural ) ); ?>
                                                    </span>
                                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="up" aria-label="<?php echo esc_attr( $plugin->localize_label( 'Augmenter le nombre d’invités', 'Increase guest count' ) ); ?>">+</button>
                                                    <input type="hidden" id="loft_booking_guests" value="<?php echo esc_attr( $default_total_guests ); ?>" />
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" id="nd_booking_archive_form_guests" name="nd_booking_archive_form_guests" value="<?php echo esc_attr( $default_total_guests ); ?>" />

                                        <div class="loft-search-toolbar__field loft-search-toolbar__field--actions">
                                            <button type="submit" class="loft-search-card__btn loft-search-card__btn--primary loft-search-toolbar__submit"><?php echo esc_html( $plugin->get_string( 'search_submit_label' ) ); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="loft1325-mobile-home__features" aria-labelledby="loft1325-mobile-features-heading">
            <h2 id="loft1325-mobile-features-heading" class="screen-reader-text"><?php echo esc_html( $features_heading ); ?></h2>
            <div class="loft1325-mobile-home__features-grid">
                <?php foreach ( $plugin->get_feature_cards() as $feature ) : ?>
                    <article class="loft1325-mobile-home__feature">
                        <div class="loft1325-mobile-home__feature-icon">
                            <?php if ( ! empty( $feature['image'] ) ) : ?>
                                <img src="<?php echo esc_url( $feature['image'] ); ?>" alt="" loading="lazy" />
                            <?php elseif ( ! empty( $feature['icon'] ) ) : ?>
                                <span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>" aria-hidden="true"></span>
                            <?php endif; ?>
                        </div>
                        <p class="loft1325-mobile-home__feature-title"><?php echo esc_html( $feature['title'] ); ?></p>
                        <?php if ( ! empty( $feature['description'] ) ) : ?>
                            <p class="loft1325-mobile-home__feature-description"><?php echo esc_html( $feature['description'] ); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="loft1325-mobile-home__rooms" aria-labelledby="loft1325-mobile-rooms-heading">
            <div class="loft1325-mobile-home__section-heading">
                <h2 id="loft1325-mobile-rooms-heading" class="loft1325-mobile-home__section-title"><?php echo esc_html( $plugin->get_string( 'rooms_heading' ) ); ?></h2>
                <p class="loft1325-mobile-home__section-description"><?php echo esc_html( $plugin->get_string( 'rooms_description' ) ); ?></p>
            </div>
            <div class="loft1325-mobile-home__room-list">
                <?php
                $rooms = $plugin->get_room_cards();
                if ( empty( $rooms ) ) :
                    ?>
                    <p class="loft1325-mobile-home__empty">
                        <?php echo esc_html( $empty_rooms_text ); ?>
                    </p>
                <?php else :
                    foreach ( $rooms as $room ) :
                        ?>
                        <article class="loft1325-mobile-home__room-card">
                            <?php if ( ! empty( $room['image'] ) ) : ?>
                                <a class="loft1325-mobile-home__room-media" href="<?php echo esc_url( $room['permalink'] ); ?>">
                                    <img src="<?php echo esc_url( $room['image'] ); ?>" alt="<?php echo esc_attr( $room['title'] ); ?>" loading="lazy" />
                                </a>
                            <?php endif; ?>
                            <div class="loft1325-mobile-home__room-body">
                                <?php if ( ! empty( $room['rating'] ) ) : ?>
                                    <span class="loft1325-mobile-home__room-badge" aria-label="<?php echo esc_attr( sprintf( $rating_label, $room['rating'] ) ); ?>"><?php echo esc_html( $room['rating'] ); ?></span>
                                <?php endif; ?>
                                <h3 class="loft1325-mobile-home__room-title">
                                    <a href="<?php echo esc_url( $room['permalink'] ); ?>"><?php echo esc_html( $room['title'] ); ?></a>
                                </h3>
                                <p class="loft1325-mobile-home__room-text"><?php echo esc_html( $room['excerpt'] ); ?></p>
                                <?php if ( $room['price'] ) : ?>
                                    <p class="loft1325-mobile-home__room-price">
                                        <?php
                                        $currency_suffix = $room['currency'] ? ' ' . $room['currency'] : '';
                                        echo esc_html(
                                            sprintf(
                                                /* translators: 1: price amount, 2: currency symbol */
                                                $price_label,
                                                number_format_i18n( (float) $room['price'], 0 ),
                                                $currency_suffix
                                            )
                                        );
                                        ?>
                                        <span class="loft1325-mobile-home__room-price-unit"><?php echo esc_html( $per_night_label ); ?></span>
                                    </p>
                                <?php endif; ?>
                                <div class="loft1325-mobile-home__room-actions">
                                    <a class="loft1325-mobile-home__btn loft1325-mobile-home__btn--secondary" href="<?php echo esc_url( $room['permalink'] ); ?>">
                                        <?php echo esc_html( $plugin->get_string( 'rooms_button_label' ) ); ?>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
            <div class="loft1325-mobile-home__room-footer">
                <a class="loft1325-mobile-home__btn loft1325-mobile-home__btn--ghost" href="<?php echo esc_url( $rooms_archive ); ?>">
                    <?php echo esc_html( $plugin->get_string( 'rooms_view_all_label' ) ); ?>
                </a>
            </div>
        </section>

        <section class="loft1325-mobile-home__cta" aria-labelledby="loft1325-mobile-cta-heading">
            <div class="loft1325-mobile-home__cta-inner">
                <h2 id="loft1325-mobile-cta-heading" class="loft1325-mobile-home__cta-title"><?php echo esc_html( $plugin->get_string( 'cta_heading' ) ); ?></h2>
                <p class="loft1325-mobile-home__cta-text"><?php echo esc_html( $plugin->get_string( 'cta_description' ) ); ?></p>
                <div class="loft1325-mobile-home__cta-actions">
                    <?php if ( $plugin->get_string( 'cta_primary_label' ) ) : ?>
                        <a class="loft1325-mobile-home__btn loft1325-mobile-home__btn--primary" href="<?php echo esc_url( $plugin->get_string( 'cta_primary_url' ) ); ?>">
                            <?php echo esc_html( $plugin->get_string( 'cta_primary_label' ) ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $plugin->get_string( 'cta_secondary_label' ) ) : ?>
                        <a class="loft1325-mobile-home__btn loft1325-mobile-home__btn--ghost" href="<?php echo esc_url( $plugin->get_string( 'cta_secondary_url' ) ); ?>">
                            <?php echo esc_html( $plugin->get_string( 'cta_secondary_label' ) ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

</div>

<?php
get_footer();
