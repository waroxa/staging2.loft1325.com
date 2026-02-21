<?php
/**
 * Mobile booking/checkout template aligned with Template-11 styling.
 *
 * @package marina-child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! have_posts() ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

the_post();

$page_id      = get_the_ID();
$page_title   = get_the_title();
$page_content = (string) get_post_field( 'post_content', $page_id );

$booking_context = function_exists( 'marina_child_get_mobile_booking_flow_context' ) ? marina_child_get_mobile_booking_flow_context() : '';
if ( '' === $booking_context ) {
    $booking_context = 'booking';
}

$current_lang = function_exists( 'trp_get_current_language' ) ? trp_get_current_language() : determine_locale();
$current_lang = strtolower( substr( (string) $current_lang, 0, 2 ) );
$fr_url       = function_exists( 'marina_child_get_language_switch_url' ) ? marina_child_get_language_switch_url( 'fr' ) : add_query_arg( 'lang', 'fr' );
$en_url       = function_exists( 'marina_child_get_language_switch_url' ) ? marina_child_get_language_switch_url( 'en' ) : add_query_arg( 'lang', 'en' );

$intro_text = 'booking' === $booking_context
    ? __( 'Sélectionnez vos options et validez en quelques étapes claires.', 'marina-child' )
    : __( 'Finalisez votre réservation avec un paiement sécurisé.', 'marina-child' );

$page_content_without_trustindex = preg_replace( '/\[trustindex[^\]]*\]/i', '', $page_content );
$main_content                    = apply_filters( 'the_content', (string) $page_content_without_trustindex );

$trustindex_markup = '';
if ( preg_match_all( '/\[(trustindex[^\]]*)\]/i', $page_content, $trustindex_matches ) ) {
    foreach ( $trustindex_matches[1] as $trustindex_shortcode ) {
        $trustindex_markup .= do_shortcode( '[' . trim( (string) $trustindex_shortcode ) . ']' );
    }
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'mobile-template-booking-flow mobile-template-booking-flow-template-11' ); ?>>
<?php wp_body_open(); ?>
<main class="mobile-shell mobile-booking-flow-shell" data-booking-context="<?php echo esc_attr( $booking_context ); ?>">
    <header class="header booking-flow-header">
        <div class="header-inner booking-flow-header__inner">
            <button class="icon-button booking-flow-icon-button" type="button" aria-label="Menu">≡</button>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="booking-flow-logo-wrap" aria-label="Lofts 1325">
                <img
                    class="logo"
                    src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
                    alt="Lofts 1325"
                />
            </a>
            <div class="booking-flow-lang-switch" aria-label="Langue">
                <a href="<?php echo esc_url( $fr_url ); ?>" class="<?php echo 'fr' === $current_lang ? 'is-active' : ''; ?>">FR</a>
                <span>|</span>
                <a href="<?php echo esc_url( $en_url ); ?>" class="<?php echo 'en' === $current_lang ? 'is-active' : ''; ?>">EN</a>
            </div>
        </div>
    </header>

    <section class="booking-flow-content">
        <section class="booking-flow-intro booking-flow-panel">
            <h1 class="booking-flow-title"><?php echo esc_html( $page_title ); ?></h1>
            <p class="booking-flow-lead"><?php echo esc_html( $intro_text ); ?></p>
        </section>

        <section class="booking-flow-panel booking-flow-panel--main" data-booking-main-content>
            <?php echo $main_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </section>

        <?php if ( '' !== trim( $trustindex_markup ) ) : ?>
            <section class="booking-flow-panel booking-flow-reviews" data-booking-trustindex-end>
                <?php echo $trustindex_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </section>
        <?php endif; ?>
    </section>
</main>
<?php wp_footer(); ?>
</body>
</html>
