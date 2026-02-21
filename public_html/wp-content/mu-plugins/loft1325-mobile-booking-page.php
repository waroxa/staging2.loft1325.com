<?php
/**
 * Plugin Name: Loft1325 Mobile Booking Page
 * Description: Forces a dedicated mobile-only layout for ND Booking room-selection pages.
 * Author: Loft1325 Automation
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Determine whether the current request is the ND booking page that needs the custom mobile layout.
 *
 * @return bool
 */
function loft1325_is_target_mobile_booking_page() {
    if ( is_admin() || is_feed() || is_embed() ) {
        return false;
    }

    if ( ! wp_is_mobile() ) {
        return false;
    }

    $booking_page_id = (int) get_option( 'nd_booking_booking_page' );

    if ( $booking_page_id > 0 && is_page( $booking_page_id ) ) {
        return true;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

    if ( '' === $request_path ) {
        return false;
    }

    $request_path = strtolower( $request_path );

    return ( 0 === strpos( $request_path, 'nd-booking-pages/nd-booking-page' ) )
        || ( 0 === strpos( $request_path, 'fr/nd-booking-pages/nd-booking-page' ) )
        || ( 0 === strpos( $request_path, 'en/nd-booking-pages/nd-booking-page' ) );
}

/**
 * Add a body class used by the mobile booking page stylesheet.
 *
 * @param array $classes Existing body classes.
 *
 * @return array
 */
function loft1325_mobile_booking_body_class( array $classes ) {
    if ( loft1325_is_target_mobile_booking_page() ) {
        $classes[] = 'loft1325-mobile-booking-active';
    }

    return $classes;
}
add_filter( 'body_class', 'loft1325_mobile_booking_body_class', 30 );

/**
 * Enqueue mobile booking page overrides.
 */
function loft1325_enqueue_mobile_booking_styles() {
    if ( ! loft1325_is_target_mobile_booking_page() ) {
        return;
    }

    $css = <<<'CSS'
@media (max-width: 767px) {
    body.loft1325-mobile-booking-active {
        --loft-mobile-bg: #ffffff;
        --loft-mobile-text: #0b0b0b;
        --loft-mobile-muted: #6b6b6b;
        --loft-mobile-border: #111111;
        --loft-mobile-divider: #e9e9e9;
        --primary: #0b0b0b;
        --accent: #0b0b0b;
        --link: #0b0b0b;
        background: #ffffff !important;
        color: #0b0b0b !important;
    }

    body.loft1325-mobile-booking-active a,
    body.loft1325-mobile-booking-active a:visited,
    body.loft1325-mobile-booking-active .nd_booking_link,
    body.loft1325-mobile-booking-active .woocommerce a {
        color: #0b0b0b !important;
    }

    body.loft1325-mobile-booking-active *,
    body.loft1325-mobile-booking-active *::before,
    body.loft1325-mobile-booking-active *::after {
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    body.loft1325-mobile-booking-active #nd_options_footer_6,
    body.loft1325-mobile-booking-active .elementor-element.elementor-element-358214a,
    body.loft1325-mobile-booking-active .elementor-element.elementor-element-4b80259c,
    body.loft1325-mobile-booking-active .elementor-element.elementor-element-9841855,
    body.loft1325-mobile-booking-active .elementor-element.elementor-element-68ddb8e {
        display: none !important;
    }

    body.loft1325-mobile-booking-active .nd_options_container.nd_options_clearfix {
        width: 100% !important;
        max-width: 430px;
        margin: 0 auto !important;
        float: none !important;
        padding: 0 16px 28px;
        box-sizing: border-box;
    }

    body.loft1325-mobile-booking-active .nd_booking_section,
    body.loft1325-mobile-booking-active .woocommerce,
    body.loft1325-mobile-booking-active .woocommerce-checkout,
    body.loft1325-mobile-booking-active .woocommerce-cart {
        background: #ffffff !important;
        color: #0b0b0b !important;
    }

    body.loft1325-mobile-booking-active input[type="text"],
    body.loft1325-mobile-booking-active input[type="email"],
    body.loft1325-mobile-booking-active input[type="tel"],
    body.loft1325-mobile-booking-active input[type="number"],
    body.loft1325-mobile-booking-active input[type="date"],
    body.loft1325-mobile-booking-active input[type="password"],
    body.loft1325-mobile-booking-active select,
    body.loft1325-mobile-booking-active textarea,
    body.loft1325-mobile-booking-active .select2-selection,
    body.loft1325-mobile-booking-active .woocommerce form .form-row input.input-text,
    body.loft1325-mobile-booking-active .woocommerce form .form-row textarea {
        background: #ffffff !important;
        color: #0b0b0b !important;
        border: 2px solid #111111 !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_yellow,
    body.loft1325-mobile-booking-active .nd_booking_button,
    body.loft1325-mobile-booking-active .woocommerce a.button,
    body.loft1325-mobile-booking-active .woocommerce button.button,
    body.loft1325-mobile-booking-active .woocommerce input.button,
    body.loft1325-mobile-booking-active #place_order,
    body.loft1325-mobile-booking-active .button,
    body.loft1325-mobile-booking-active button[type="submit"] {
        background: #0b0b0b !important;
        border: 1px solid #0b0b0b !important;
        color: #ffffff !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em;
        width: 100%;
    }

    body.loft1325-mobile-booking-active .nd_booking_border,
    body.loft1325-mobile-booking-active .nd_booking_border_box,
    body.loft1325-mobile-booking-active .woocommerce table,
    body.loft1325-mobile-booking-active .shop_table,
    body.loft1325-mobile-booking-active .woocommerce-checkout-review-order {
        border-color: #e9e9e9 !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_greydark,
    body.loft1325-mobile-booking-active .nd_booking_bg_greydark_2 {
        background: #0b0b0b !important;
        border-color: #0b0b0b !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_greydark *,
    body.loft1325-mobile-booking-active .nd_booking_bg_greydark_2 * {
        color: #ffffff !important;
    }
}
CSS;

    $target_handle = wp_style_is( 'nd_booking_mobile_flow', 'enqueued' ) ? 'nd_booking_mobile_flow' : 'marina-child-header-fixes';

    if ( ! wp_style_is( $target_handle, 'enqueued' ) ) {
        wp_register_style( 'loft1325-mobile-booking-inline', false, array(), '1.0.0' );
        wp_enqueue_style( 'loft1325-mobile-booking-inline' );
        $target_handle = 'loft1325-mobile-booking-inline';
    }

    wp_add_inline_style( $target_handle, $css );
}
add_action( 'wp_enqueue_scripts', 'loft1325_enqueue_mobile_booking_styles', 200 );
