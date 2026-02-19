<?php
/**
 * Plugin Name: Loft1325 Booking Hub
 * Description: Mobile-first admin booking hub for Loft1325 with ButterflyMX integration.
 * Version: 0.2.1
 * Author: Loft1325
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LOFT1325_BOOKING_HUB_VERSION', '0.2.1' );
define( 'LOFT1325_BOOKING_HUB_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOFT1325_BOOKING_HUB_URL', plugin_dir_url( __FILE__ ) );

define( 'LOFT1325_SETTINGS_OPTION', 'loft1325_booking_hub_settings' );

define( 'LOFT1325_PASSWORD_META_KEY', 'loft1325_booking_hub_unlock_until' );

require_once LOFT1325_BOOKING_HUB_PATH . 'includes/helpers.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-db.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-security.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-api-butterflymx.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-lofts.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-bookings.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-operations.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-admin-pages.php';
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-frontend-pages.php';

add_filter( 'cron_schedules', function ( $schedules ) {
    if ( ! isset( $schedules['loft1325_every_15_minutes'] ) ) {
        $schedules['loft1325_every_15_minutes'] = array(
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 15 Minutes', 'loft1325-booking-hub' ),
        );
    }

    return $schedules;
} );

register_activation_hook( __FILE__, array( 'Loft1325_DB', 'activate' ) );
register_activation_hook( __FILE__, array( 'Loft1325_API_ButterflyMX', 'ensure_refresh_schedule' ) );
register_deactivation_hook( __FILE__, function () {
    wp_clear_scheduled_hook( 'loft1325_butterflymx_refresh_tokens' );
} );

add_action( 'plugins_loaded', function () {
    Loft1325_Security::boot();
    Loft1325_DB::boot();
    Loft1325_API_ButterflyMX::boot();
    Loft1325_Lofts::boot();
    Loft1325_Bookings::boot();
    Loft1325_Operations::boot();
    Loft1325_Admin_Pages::boot();
    Loft1325_Frontend_Pages::boot();
} );

add_filter( 'login_display_language_dropdown', '__return_false' );

add_action( 'login_head', function () {
    echo '<style>.language-switcher{display:none !important;}</style>';
} );
