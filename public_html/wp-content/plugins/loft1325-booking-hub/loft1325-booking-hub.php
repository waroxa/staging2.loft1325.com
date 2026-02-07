<?php
/**
 * Plugin Name: Loft1325 Booking Hub
 * Description: Mobile-first admin booking hub for Loft1325 with ButterflyMX integration.
 * Version: 0.1.0
 * Author: Loft1325
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LOFT1325_BOOKING_HUB_VERSION', '0.1.0' );
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
require_once LOFT1325_BOOKING_HUB_PATH . 'includes/class-admin-pages.php';

register_activation_hook( __FILE__, array( 'Loft1325_DB', 'activate' ) );

add_action( 'plugins_loaded', function () {
    Loft1325_Security::boot();
    Loft1325_DB::boot();
    Loft1325_Lofts::boot();
    Loft1325_Bookings::boot();
    Loft1325_Admin_Pages::boot();
} );
