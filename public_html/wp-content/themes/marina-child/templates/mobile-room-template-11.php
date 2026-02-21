<?php
/**
 * Mobile room template aligned with Template-11 styling.
 *
 * @package marina-child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! have_posts() ) {
    wp_safe_redirect( home_url( '/rooms/' ) );
    exit;
}

the_post();

$room_id      = get_the_ID();
$room_title   = get_the_title();
$room_content = (string) get_post_field( 'post_content', $room_id );

$trustindex_shortcodes = array();
if ( preg_match_all( '/\[(trustindex[^\]]*)\]/i', $room_content, $matches ) ) {
    $trustindex_shortcodes = array_map(
        static function ( $shortcode ) {
            return '[' . trim( (string) $shortcode ) . ']';
        },
        $matches[1]
    );
}

$room_content_without_trustindex = preg_replace( '/\[trustindex[^\]]*\]/i', '', $room_content );
$room_text                       = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $room_content_without_trustindex ) ) ) );
$description                     = '' !== $room_text ? wp_trim_words( $room_text, 32, '…' ) : 'Une expérience éditoriale, intime et ultra premium au coeur de Montréal.';

$raw_text_blocks = preg_split( '/\n{2,}/', wp_strip_all_tags( strip_shortcodes( $room_content_without_trustindex ) ) );
$text_blocks     = array_values(
    array_filter(
        array_map(
            static function ( $line ) {
                $line = trim( preg_replace( '/^[\x{2022}\*\-\s]+/u', '', (string) $line ) );
                return '' !== $line ? $line : null;
            },
            is_array( $raw_text_blocks ) ? $raw_text_blocks : array()
        )
    )
);

$price = get_post_meta( $room_id, 'nd_booking_meta_box_min_price', true );
if ( '' === $price ) {
    $price = get_post_meta( $room_id, 'nd_booking_meta_box_price', true );
}

$room_size = get_post_meta( $room_id, 'nd_booking_meta_box_room_size', true );
$min_night = get_post_meta( $room_id, 'nd_booking_meta_box_min_booking_day', true );

$services = (string) get_post_meta( $room_id, 'nd_booking_meta_box_additional_services', true );
if ( '' === trim( $services ) ) {
    $services = (string) get_post_meta( $room_id, 'nd_booking_meta_box_normal_services', true );
}

$amenities = preg_split( '/[,;\n\|]+/', $services );
$amenities = array_values(
    array_filter(
        array_map(
            static function ( $item ) {
                return trim( wp_strip_all_tags( (string) $item ) );
            },
            is_array( $amenities ) ? $amenities : array()
        )
    )
);

if ( empty( $amenities ) ) {
    $amenities = array( 'Wi-Fi rapide', 'Cuisine équipée', 'Salle de bain marbre', 'Vue ville', 'Arrivée autonome', 'Literie hôtelière' );
}

$images = array();

$featured = get_the_post_thumbnail_url( $room_id, 'large' );
if ( $featured ) {
    $images[] = $featured;
}

$meta_image = get_post_meta( $room_id, 'nd_booking_meta_box_image', true );
if ( $meta_image ) {
    $images[] = esc_url_raw( $meta_image );
}

$attachments = get_attached_media( 'image', $room_id );
if ( ! empty( $attachments ) ) {
    foreach ( $attachments as $attachment ) {
        $image_url = wp_get_attachment_image_url( $attachment->ID, 'large' );

        if ( $image_url ) {
            $images[] = $image_url;
        }
    }
}

$images = array_values( array_unique( array_filter( $images ) ) );

if ( empty( $images ) ) {
    $images[] = home_url( '/wp-content/uploads/2022/04/room01.jpg' );
}

$capacity = '2 PERSONNES';
if ( stripos( $room_title, 'double' ) !== false ) {
    $capacity = '4 PERSONNES';
} elseif ( stripos( $room_title, 'penthouse' ) !== false ) {
    $capacity = '6 PERSONNES';
}

$price_label = '' !== $price ? trim( $price ) . ' CAD' : '—';
$booking_url = home_url( '/rooms/' );

$current_lang = function_exists( 'trp_get_current_language' ) ? trp_get_current_language() : determine_locale();
$current_lang = strtolower( substr( (string) $current_lang, 0, 2 ) );

$fr_url = function_exists( 'marina_child_get_language_switch_url' ) ? marina_child_get_language_switch_url( 'fr' ) : add_query_arg( 'lang', 'fr' );
$en_url = function_exists( 'marina_child_get_language_switch_url' ) ? marina_child_get_language_switch_url( 'en' ) : add_query_arg( 'lang', 'en' );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'mobile-template-rooms mobile-template-11-room' ); ?>>
<?php wp_body_open(); ?>
<main class="mobile-shell mobile-room-shell" data-room-gallery="<?php echo esc_attr( wp_json_encode( $images ) ); ?>">
    <header class="header room-header">
        <div class="header-inner room-header__inner">
            <button class="icon-button room-icon-button" type="button" aria-label="Menu">≡</button>
            <img
                class="logo"
                src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
                alt="Lofts 1325"
            />
            <div class="room-lang-switch" aria-label="Langue">
                <a href="<?php echo esc_url( $fr_url ); ?>" class="<?php echo 'fr' === $current_lang ? 'is-active' : ''; ?>">FR</a>
                <span>|</span>
                <a href="<?php echo esc_url( $en_url ); ?>" class="<?php echo 'en' === $current_lang ? 'is-active' : ''; ?>">EN</a>
            </div>
        </div>
    </header>

    <section class="room-gallery" data-gallery-root>
        <div class="room-gallery__viewport">
            <button type="button" class="room-gallery__arrow room-gallery__arrow--prev" data-gallery-prev aria-label="Image précédente">←</button>
            <img class="room-gallery__image" data-gallery-image src="<?php echo esc_url( $images[0] ); ?>" alt="<?php echo esc_attr( $room_title ); ?>" loading="eager" />
            <button type="button" class="room-gallery__arrow room-gallery__arrow--next" data-gallery-next aria-label="Image suivante">→</button>
        </div>
        <div class="room-gallery__dots" data-gallery-dots></div>
        <div class="room-gallery__thumbs" data-gallery-thumbs></div>
    </section>

    <section class="room-content">
        <h1 class="room-title-main"><?php echo esc_html( $room_title ); ?></h1>
        <p class="room-description"><?php echo esc_html( $description ); ?></p>

        <section class="room-panel">
            <h2>CARACTÉRISTIQUES</h2>
            <div class="room-feature-grid">
                <div class="room-feature-row">
                    <span class="label">CAPACITÉ</span>
                    <strong><?php echo esc_html( $capacity ); ?></strong>
                    <small>Literie premium & ambiance calme</small>
                </div>
                <div class="room-feature-row">
                    <span class="label">SURFACE</span>
                    <strong><?php echo esc_html( '' !== $room_size ? $room_size . ' m²' : '82 m²' ); ?></strong>
                    <small>Espace loft ouvert et lumineux</small>
                </div>
                <div class="room-feature-row">
                    <span class="label">NUITS MINIMALES</span>
                    <strong><?php echo esc_html( '' !== $min_night ? $min_night : '1' ); ?></strong>
                    <small>Check-in autonome 24/7</small>
                </div>
            </div>
        </section>

        <section class="room-panel">
            <h2>ÉQUIPEMENTS</h2>
            <div class="room-amenities">
                <?php foreach ( $amenities as $amenity ) : ?>
                    <span class="room-amenity-tag"><?php echo esc_html( strtoupper( $amenity ) ); ?></span>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if ( ! empty( $text_blocks ) ) : ?>
            <section class="room-panel room-text-panel">
                <h2>DÉTAILS</h2>
                <?php foreach ( $text_blocks as $text_block ) : ?>
                    <p><?php echo esc_html( $text_block ); ?></p>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section class="room-panel room-pricing">
            <div class="room-pricing__row">
                <span>Tarif du jour</span>
                <strong><?php echo esc_html( $price_label ); ?></strong>
            </div>
            <a class="primary-button room-cta" href="<?php echo esc_url( $booking_url ); ?>">RÉSERVER MAINTENANT</a>
        </section>

        <section class="room-panel room-reviews-panel">
            <?php
            if ( ! empty( $trustindex_shortcodes ) ) {
                foreach ( $trustindex_shortcodes as $trustindex_shortcode ) {
                    echo do_shortcode( $trustindex_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            } else {
                echo do_shortcode( '[trustindex no-registration=google]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </section>
    </section>
</main>
<?php wp_footer(); ?>
</body>
</html>
