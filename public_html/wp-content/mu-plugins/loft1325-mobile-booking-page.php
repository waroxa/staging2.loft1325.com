<?php
/**
 * Plugin Name: Loft1325 Mobile Booking Page
 * Description: Forces a dedicated mobile-only layout for ND Booking room-selection pages.
 * Author: Loft1325 Automation
 * Version: 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function loft1325_is_target_mobile_unified_page() {
    if ( is_admin() || is_feed() || is_embed() || ! wp_is_mobile() ) {
        return false;
    }

    if ( is_singular( 'nd_booking_cpt_1' ) ) {
        return true;
    }

    $booking_page_id  = (int) get_option( 'nd_booking_booking_page' );
    $checkout_page_id = (int) get_option( 'nd_booking_checkout_page' );

    if ( ( $booking_page_id > 0 && is_page( $booking_page_id ) ) || ( $checkout_page_id > 0 && is_page( $checkout_page_id ) ) ) {
        return true;
    }

    $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

    if ( '' === $request_path ) {
        return false;
    }

    $request_path = strtolower( $request_path );

    return ( 0 === strpos( $request_path, 'rooms/' ) )
        || ( 0 === strpos( $request_path, 'fr/rooms/' ) )
        || ( 0 === strpos( $request_path, 'en/rooms/' ) )
        || ( 0 === strpos( $request_path, 'nd-booking-pages/nd-booking-page' ) )
        || ( 0 === strpos( $request_path, 'fr/nd-booking-pages/nd-booking-page' ) )
        || ( 0 === strpos( $request_path, 'en/nd-booking-pages/nd-booking-page' ) );
}

function loft1325_mobile_unified_body_class( array $classes ) {
    if ( loft1325_is_target_mobile_unified_page() ) {
        $classes[] = 'loft1325-mobile-unified-active';
    }

    return $classes;
}
add_filter( 'body_class', 'loft1325_mobile_unified_body_class', 35 );

function loft1325_mobile_unified_enqueue_styles() {
    if ( ! loft1325_is_target_mobile_unified_page() ) {
        return;
    }

    $template_11_path = ABSPATH . 'mobile-templates/assets/template-11.css';
    $template_11_url  = home_url( '/mobile-templates/assets/template-11.css' );
    $template_11_ver  = file_exists( $template_11_path ) ? (string) filemtime( $template_11_path ) : '1.0.0';

    wp_enqueue_style( 'loft1325-mobile-template-11-base', $template_11_url, array(), $template_11_ver );

    $path = __DIR__ . '/assets/mobile-unified-template.css';
    $url  = plugin_dir_url( __FILE__ ) . 'assets/mobile-unified-template.css';
    $ver  = file_exists( $path ) ? (string) filemtime( $path ) : '1.3.0';

    wp_enqueue_style( 'loft1325-mobile-unified-template', $url, array( 'loft1325-mobile-template-11-base' ), $ver );
}
add_action( 'wp_enqueue_scripts', 'loft1325_mobile_unified_enqueue_styles', 210 );

function loft1325_mobile_get_language_code() {
    $language = 'fr';

    if ( function_exists( 'trp_get_current_language' ) ) {
        $language = (string) trp_get_current_language();
    } else {
        $locale   = function_exists( 'determine_locale' ) ? (string) determine_locale() : (string) get_locale();
        $language = substr( strtolower( $locale ), 0, 2 );
    }

    return ( 'en' === strtolower( substr( $language, 0, 2 ) ) ) ? 'en' : 'fr';
}

function loft1325_mobile_get_language_url( $language ) {
    $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );

    if ( function_exists( 'trp_get_url_for_language' ) ) {
        return trp_get_url_for_language( $current_url, $language );
    }

    return add_query_arg( 'lang', $language, $current_url );
}

function loft1325_mobile_unified_render_header() {
    if ( ! loft1325_is_target_mobile_unified_page() ) {
        return;
    }

    $language      = loft1325_mobile_get_language_code();
    $fr_url        = loft1325_mobile_get_language_url( 'fr' );
    $en_url        = loft1325_mobile_get_language_url( 'en' );
    $header_tpl    = __DIR__ . '/templates/mobile-base-header.php';

    if ( file_exists( $header_tpl ) ) {
        include $header_tpl;
        return;
    }
}
add_action( 'wp_body_open', 'loft1325_mobile_unified_render_header', 3 );
