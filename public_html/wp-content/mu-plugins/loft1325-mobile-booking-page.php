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
        background: #f5f6f8 !important;
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
        padding: 0 14px 28px;
        box-sizing: border-box;
    }

    body.loft1325-mobile-booking-active .elementor {
        background: transparent !important;
    }

    body.loft1325-mobile-booking-active #booking_page_shortcode {
        margin-top: 12px;
    }

    body.loft1325-mobile-booking-active #booking_page_shortcode > .nd_booking_section,
    body.loft1325-mobile-booking-active #booking_page_shortcode > .nd_booking_section > .nd_booking_section,
    body.loft1325-mobile-booking-active #booking_page_shortcode .nd_booking_width_100_percentage.nd_booking_border_box {
        background: #ffffff !important;
        border-radius: 24px;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    }

    body.loft1325-mobile-booking-active .nd_booking_section {
        overflow: hidden;
    }

    body.loft1325-mobile-booking-active .nd_booking_section .nd_booking_section {
        border-radius: 0;
        border: 0 !important;
        box-shadow: none;
    }

    body.loft1325-mobile-booking-active .nd_booking_tax_breakdown [data-tax-key="total_tax"] {
        display: none !important;
    }

    body.loft1325-mobile-booking-active input[type="text"],
    body.loft1325-mobile-booking-active input[type="email"],
    body.loft1325-mobile-booking-active input[type="tel"],
    body.loft1325-mobile-booking-active input[type="number"],
    body.loft1325-mobile-booking-active input[type="date"],
    body.loft1325-mobile-booking-active input[type="password"],
    body.loft1325-mobile-booking-active select,
    body.loft1325-mobile-booking-active textarea,
    body.loft1325-mobile-booking-active .select2-selection {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
    }

    body.loft1325-mobile-booking-active input::placeholder,
    body.loft1325-mobile-booking-active textarea::placeholder {
        color: #64748b !important;
        opacity: 1 !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_section *,
    body.loft1325-mobile-booking-active .woocommerce *,
    body.loft1325-mobile-booking-active .elementor-widget-container * {
        color: #0f172a !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_greydark,
    body.loft1325-mobile-booking-active .nd_booking_bg_greydark_2 {
        background: #0f172a !important;
        border-color: #0f172a !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_greydark *,
    body.loft1325-mobile-booking-active .nd_booking_bg_greydark_2 * {
        color: #ffffff !important;
    }

    body.loft1325-mobile-booking-active .nd_booking_bg_yellow,
    body.loft1325-mobile-booking-active .nd_booking_button,
    body.loft1325-mobile-booking-active .woocommerce a.button,
    body.loft1325-mobile-booking-active .woocommerce button.button,
    body.loft1325-mobile-booking-active .woocommerce input.button,
    body.loft1325-mobile-booking-active #place_order {
        border-radius: 999px !important;
        background: #0f172a !important;
        border: 1px solid #0f172a !important;
        color: #ffffff !important;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    body.loft1325-mobile-booking-active .nd_booking_width_100_percentage > p,
    body.loft1325-mobile-booking-active .nd_booking_width_100_percentage > h1,
    body.loft1325-mobile-booking-active .nd_booking_width_100_percentage > h2 {
        padding: 24px 20px 10px;
        margin: 0;
        text-align: center;
    }

    body.loft1325-mobile-booking-active .nd_booking_width_100_percentage > a {
        display: block !important;
        width: calc(100% - 40px);
        margin: 0 20px 24px;
        text-align: center;
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
