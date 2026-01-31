<?php
/*
Plugin Name: WP Loft Booking Plugin
Plugin URI: https://loft1325.com
Description: Custom booking plugin for managing room reservations and virtual keys.
Version: 1.0
Author: Maria Garcia
Author URI: https://loft1325.com
License: GPL2
*/

defined('ABSPATH') || exit;

// Include all necessary files
require_once plugin_dir_path(__FILE__) . 'includes/database/db-setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/database/db-cleanup.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/branches.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/lofts.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/bookings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/loft-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/butterflymx-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/access-points.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/payment-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/tenants.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/email-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/email-templates.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/email-jobs.php';
require_once plugin_dir_path(__FILE__) . 'includes/integrations/butterflymx.php';
require_once plugin_dir_path(__FILE__) . 'includes/integrations/email-provider.php';
require_once plugin_dir_path(__FILE__) . 'includes/integrations/booking-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/integrations/amelia-hooks.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/booking-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/search-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/display-results.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/loft-types-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/cron/cron-jobs.php';
require_once plugin_dir_path(__FILE__) . 'includes/calendar/google-calendar.php';
require_once plugin_dir_path(__FILE__) . 'includes/calendar/google-oauth-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/calendar/cleaning-calendar.php';
require_once plugin_dir_path(__FILE__) . 'includes/calendar/keychain-calendar.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/keychains.php';

/**
 * Run a callback while suppressing wp_die output.
 */
function wp_loft_booking_run_safely( callable $callback ) {
    add_filter( 'wp_die_handler', 'wp_loft_booking_noop_die_handler' );
    add_filter( 'wp_die_ajax_handler', 'wp_loft_booking_noop_die_handler' );
    try {
        $callback();
    } finally {
        remove_filter( 'wp_die_handler', 'wp_loft_booking_noop_die_handler' );
        remove_filter( 'wp_die_ajax_handler', 'wp_loft_booking_noop_die_handler' );
    }
}

function wp_loft_booking_noop_die_handler() {
    return 'wp_loft_booking_noop_die';
}

function wp_loft_booking_noop_die( $message = '', $title = '', $args = array() ) {}

/**
 * Trigger a unit sync either asynchronously or immediately.
 *
 * @param string $reason Optional context for logging.
 */
function wp_loft_booking_trigger_unit_sync( $reason = '' ) {
    if ( ! function_exists( 'wp_loft_booking_sync_units' ) ) {
        return;
    }

    $reason      = trim( (string) $reason );
    $log_suffix  = '' !== $reason ? ' [' . $reason . ']' : '';
    $sync_planned = false;

    if ( function_exists( 'wp_schedule_single_event' ) ) {
        $base_timestamp = time() + 5;

        for ( $attempt = 0; $attempt < 3; $attempt++ ) {
            $timestamp = $base_timestamp + $attempt;

            if ( wp_schedule_single_event( $timestamp, 'wp_loft_booking_sync_units' ) ) {
                error_log( sprintf( '🗓️ Scheduled unit sync%s for %s.', $log_suffix, gmdate( 'c', $timestamp ) ) );
                $sync_planned = true;
                break;
            }
        }
    }

    if ( $sync_planned ) {
        return;
    }

    if ( function_exists( 'wp_loft_booking_run_safely' ) ) {
        wp_loft_booking_run_safely( 'wp_loft_booking_sync_units' );
    } else {
        wp_loft_booking_sync_units();
    }

    error_log( sprintf( '♻️ Triggered immediate unit sync%s.', $log_suffix ) );
}

/**
 * Sync tenants, keychains and units in sequence without exiting.
 */
function wp_loft_booking_full_sync() {
    if ( function_exists( 'wp_loft_booking_sync_units' ) ) {
        $result = wp_loft_booking_sync_units();

        if ( is_wp_error( $result ) ) {
            error_log( '[WP Loft Booking] Full sync failed: ' . $result->get_error_message() );
        }

        return $result;
    }

    $results = [];

    if ( function_exists( 'wp_loft_booking_fetch_and_save_tenants' ) ) {
        $results['tenants'] = wp_loft_booking_fetch_and_save_tenants();
    }

    if ( function_exists( 'keychains_page_function' ) ) {
        $results['keychains'] = keychains_page_function();
    }

    if ( function_exists( 'wp_loft_booking_sync_units_only' ) ) {
        $results['units'] = wp_loft_booking_sync_units_only();
    }

    return $results;
}




// Enqueue scripts and styles
function wp_loft_booking_enqueue_scripts() {
    wp_enqueue_style('custom-loft-styles', plugin_dir_url(__FILE__) . 'assets/css/custom-loft-style.css');
    wp_enqueue_script('custom-loft-script', plugin_dir_url(__FILE__) . 'assets/js/custom-loft-script.js', ['jquery'], '1.0', true);
    wp_localize_script('custom-loft-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);

    $should_enqueue_search_fix = is_search();

    if ( ! $should_enqueue_search_fix ) {
        $nd_booking_query_params = array(
            'nd_booking_archive_form_date_range_from',
            'nd_booking_archive_form_date_range_to',
            'nd_booking_archive_form_guests',
            'nd_booking_archive_form_services',
            'nd_booking_archive_form_additional_services',
            'nd_booking_archive_form_branch_stars',
            'nd_booking_archive_form_branches',
            'nd_booking_archive_form_max_price_for_day',
        );

        foreach ( $nd_booking_query_params as $query_param ) {
            if ( isset( $_GET[ $query_param ] ) && '' !== $_GET[ $query_param ] ) {
                $should_enqueue_search_fix = true;
                break;
            }
        }
    }

    if ( $should_enqueue_search_fix ) {
        $search_fix_path = plugin_dir_path( __FILE__ ) . 'assets/js/nd-booking-mobile-masonry-fix.js';

        if ( file_exists( $search_fix_path ) && is_readable( $search_fix_path ) ) {
            wp_enqueue_script(
                'wp-loft-booking-search-fix',
                plugin_dir_url( __FILE__ ) . 'assets/js/nd-booking-mobile-masonry-fix.js',
                array(),
                (string) filemtime( $search_fix_path ),
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'wp_loft_booking_enqueue_scripts');

function wp_loft_booking_apply_default_search_params() {
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }

    $has_dates = ! empty( $_GET['nd_booking_archive_form_date_range_from'] ) || ! empty( $_GET['nd_booking_archive_form_date_range_to'] );
    $has_nights = ! empty( $_GET['nd_booking_archive_form_nights'] );
    $has_guests = ! empty( $_GET['nd_booking_archive_form_guests'] );

    if ( $has_dates || $has_nights || $has_guests ) {
        return;
    }

    if ( ! function_exists( 'nd_booking_booking_page' ) && ! function_exists( 'nd_booking_search_page' ) ) {
        return;
    }

    $targets = array();

    if ( function_exists( 'nd_booking_booking_page' ) ) {
        $targets[] = wp_parse_url( nd_booking_booking_page(), PHP_URL_PATH );
    }

    if ( function_exists( 'nd_booking_search_page' ) ) {
        $targets[] = wp_parse_url( nd_booking_search_page(), PHP_URL_PATH );
    }

    $targets = array_filter(
        array_map(
            static function ( $path ) {
                return '/' . trim( (string) $path, '/' );
            },
            $targets
        )
    );

    if ( empty( $targets ) ) {
        return;
    }

    global $wp;
    if ( ! $wp instanceof WP ) {
        return;
    }

    $current_path = '/' . trim( $wp->request, '/' );
    if ( ! in_array( $current_path, $targets, true ) ) {
        return;
    }

    $current_url = home_url( $wp->request );
    if ( ! empty( $_GET ) ) {
        $current_url = add_query_arg( wp_unslash( $_GET ), $current_url );
    }

    $redirect_url = add_query_arg(
        array(
            'nd_booking_archive_form_nights' => 1,
            'nd_booking_archive_form_guests' => 1,
        ),
        $current_url
    );

    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'template_redirect', 'wp_loft_booking_apply_default_search_params' );

function wp_loft_booking_enqueue_admin_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('custom-loft-script', plugin_dir_url(__FILE__) . 'assets/js/custom-loft-script.js', ['jquery'], '1.0', true);
    wp_enqueue_style('custom-loft-styles', plugin_dir_url(__FILE__) . 'assets/css/custom-loft-style.css');
}
add_action('admin_enqueue_scripts', 'wp_loft_booking_enqueue_admin_scripts');
