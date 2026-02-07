<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Bookings {
    public static function boot() {
        add_action( 'admin_post_loft1325_create_booking', array( __CLASS__, 'create_booking' ) );
        add_action( 'admin_post_loft1325_revoke_key', array( __CLASS__, 'revoke_key' ) );
    }

    public static function get_dashboard_counts() {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $today = gmdate( 'Y-m-d' );

        $checkins = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookings_table} WHERE DATE(check_in_utc) = %s AND status IN ('confirmed','checked_in')",
            $today
        ) );

        $checkouts = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookings_table} WHERE DATE(check_out_utc) = %s AND status IN ('confirmed','checked_in')",
            $today
        ) );

        $occupied = $wpdb->get_var( "SELECT COUNT(*) FROM {$bookings_table} WHERE status IN ('confirmed','checked_in')" );
        $available = $wpdb->get_results(
            "SELECT loft_type, COUNT(*) as count FROM {$lofts_table} WHERE is_active = 1 GROUP BY loft_type",
            ARRAY_A
        );

        return array(
            'checkins' => intval( $checkins ),
            'checkouts' => intval( $checkouts ),
            'occupied' => intval( $occupied ),
            'available' => $available,
        );
    }

    public static function get_bookings( $limit = 50 ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $query = $wpdb->prepare(
            "SELECT b.*, l.loft_name, l.loft_type
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            ORDER BY b.check_in_utc DESC
            LIMIT %d",
            $limit
        );

        return $wpdb->get_results( $query, ARRAY_A );
    }

    public static function create_booking() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_create_booking' );

        global $wpdb;
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';

        $check_in = loft1325_to_utc( sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) );
        $check_out = loft1325_to_utc( sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) );
        $loft_type = sanitize_text_field( wp_unslash( $_POST['loft_type'] ) );
        $loft_id = isset( $_POST['loft_id'] ) ? absint( $_POST['loft_id'] ) : 0;

        $lock_key = 'loft1325_lock_' . $loft_type;
        $wpdb->query( $wpdb->prepare( "SELECT GET_LOCK(%s, 10)", $lock_key ) );

        if ( ! $loft_id ) {
            $available = Loft1325_Lofts::get_available_by_type( $loft_type, $check_in, $check_out );
            if ( empty( $available ) ) {
                $wpdb->query( $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $lock_key ) );
                wp_safe_redirect( add_query_arg( 'loft1325_error', 'no_availability', wp_get_referer() ) );
                exit;
            }
            $loft_id = intval( $available[0]['id'] );
        }

        $conflict = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookings_table}
            WHERE loft_id = %d
            AND status IN ('confirmed','checked_in')
            AND %s < check_out_utc
            AND %s > check_in_utc",
            $loft_id,
            $check_in,
            $check_out
        ) );

        if ( $conflict ) {
            $wpdb->query( $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $lock_key ) );
            wp_safe_redirect( add_query_arg( 'loft1325_error', 'overlap', wp_get_referer() ) );
            exit;
        }

        $data = array(
            'loft_id' => $loft_id,
            'guest_name' => sanitize_text_field( wp_unslash( $_POST['guest_name'] ) ),
            'guest_email' => sanitize_email( wp_unslash( $_POST['guest_email'] ) ),
            'guest_phone' => sanitize_text_field( wp_unslash( $_POST['guest_phone'] ) ),
            'check_in_utc' => $check_in,
            'check_out_utc' => $check_out,
            'status' => 'confirmed',
            'notes' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ),
            'created_by' => get_current_user_id(),
            'created_at' => current_time( 'mysql', 1 ),
            'updated_at' => current_time( 'mysql', 1 ),
        );

        $wpdb->insert( $bookings_table, $data );
        $booking_id = $wpdb->insert_id;

        $wpdb->query( $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $lock_key ) );

        loft1325_log_action( 'booking_created', 'Booking created', array( 'booking_id' => $booking_id, 'loft_id' => $loft_id ) );

        if ( isset( $_POST['create_key'] ) ) {
            self::create_key_for_booking( $booking_id );
        }

        wp_safe_redirect( add_query_arg( 'loft1325_created', '1', wp_get_referer() ) );
        exit;
    }

    public static function create_key_for_booking( $booking_id ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bookings_table} WHERE id = %d", $booking_id ), ARRAY_A );
        if ( ! $booking ) {
            return;
        }

        $loft = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$lofts_table} WHERE id = %d", $booking['loft_id'] ), ARRAY_A );
        if ( ! $loft ) {
            return;
        }

        $settings = loft1325_get_settings();
        $tenant_id = $loft['butterfly_tenant_id'] ? absint( $loft['butterfly_tenant_id'] ) : null;
        $unit_id = $loft['butterfly_unit_id'] ? absint( $loft['butterfly_unit_id'] ) : null;

        if ( ( $tenant_id && $unit_id ) || ( ! $tenant_id && ! $unit_id ) ) {
            loft1325_log_action( 'butterflymx_error', 'Invalid tenant/unit mapping', array( 'booking_id' => $booking_id, 'loft_id' => $loft['id'] ) );
            return;
        }

        $recipients = array();
        if ( ! empty( $booking['guest_email'] ) ) {
            $recipients[] = $booking['guest_email'];
        }
        if ( ! empty( $booking['guest_phone'] ) ) {
            $recipients[] = $booking['guest_phone'];
        }

        $payload = array(
            'starts_at' => gmdate( 'c', strtotime( $booking['check_in_utc'] ) ),
            'ends_at' => gmdate( 'c', strtotime( $booking['check_out_utc'] ) ),
            'name' => str_replace(
                array( '{booking_id}', '{loft_name}', '{guest_name}' ),
                array( $booking_id, $loft['loft_name'], $booking['guest_name'] ),
                $settings['pass_naming_template']
            ),
            'access_point_ids' => array_filter( array_map( 'absint', explode( ',', $settings['default_access_point_ids'] ) ) ),
            'device_ids' => array_filter( array_map( 'absint', explode( ',', $settings['default_device_ids'] ) ) ),
            'recipients' => $recipients,
        );

        if ( $tenant_id ) {
            $payload['tenant_id'] = $tenant_id;
        }
        if ( $unit_id ) {
            $payload['unit_id'] = $unit_id;
        }

        $response = Loft1325_API_ButterflyMX::create_keychain( $payload );
        if ( is_wp_error( $response ) ) {
            loft1325_log_action( 'butterflymx_error', 'Failed to create key', array( 'booking_id' => $booking_id, 'loft_id' => $loft['id'] ) );
            return;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['id'] ) ) {
            $wpdb->update( $bookings_table, array( 'butterfly_keychain_id' => absint( $body['id'] ) ), array( 'id' => $booking_id ) );
        }
    }

    public static function revoke_key() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_revoke_key' );

        global $wpdb;
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';

        $booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
        $booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bookings_table} WHERE id = %d", $booking_id ), ARRAY_A );

        if ( ! $booking || empty( $booking['butterfly_keychain_id'] ) ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        Loft1325_API_ButterflyMX::revoke_keychain( $booking['butterfly_keychain_id'] );

        $wpdb->update( $bookings_table, array( 'butterfly_keychain_id' => null ), array( 'id' => $booking_id ) );

        wp_safe_redirect( add_query_arg( 'loft1325_revoked', '1', wp_get_referer() ) );
        exit;
    }
}
