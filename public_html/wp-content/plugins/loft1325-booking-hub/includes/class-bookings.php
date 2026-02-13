<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Bookings {
    public static function boot() {
        add_action( 'admin_post_loft1325_create_booking', array( __CLASS__, 'create_booking' ) );
        add_action( 'admin_post_loft1325_revoke_key', array( __CLASS__, 'revoke_key' ) );
        add_action( 'admin_post_loft1325_sync_keychains', array( __CLASS__, 'sync_from_butterflymx' ) );
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

    public static function get_bookings_for_range( $start_utc, $end_utc ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $query = $wpdb->prepare(
            "SELECT b.*, l.loft_name, l.loft_type
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            WHERE b.status IN ('confirmed','checked_in','tentative')
            AND %s < b.check_out_utc
            AND %s > b.check_in_utc
            ORDER BY b.check_in_utc ASC",
            $start_utc,
            $end_utc
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

    public static function sync_from_butterflymx() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_sync_keychains' );

        $response = Loft1325_API_ButterflyMX::list_keychains();
        if ( is_wp_error( $response ) ) {
            wp_safe_redirect( add_query_arg( 'loft1325_sync_error', '1', wp_get_referer() ) );
            exit;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $keychains = isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();

        $synced_count = 0;

        foreach ( $keychains as $keychain ) {
            if ( self::upsert_booking_from_keychain( $keychain ) ) {
                $synced_count++;
            }
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'loft1325_synced'       => '1',
                    'loft1325_synced_count' => $synced_count,
                ),
                wp_get_referer()
            )
        );
        exit;
    }

    private static function upsert_booking_from_keychain( $keychain ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $keychain_id = isset( $keychain['id'] ) ? absint( $keychain['id'] ) : 0;
        if ( ! $keychain_id ) {
            return false;
        }

        $tenant_id = isset( $keychain['tenant_id'] ) ? absint( $keychain['tenant_id'] ) : 0;
        $unit_id = isset( $keychain['unit_id'] ) ? absint( $keychain['unit_id'] ) : 0;

        if ( $tenant_id ) {
            $loft = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$lofts_table} WHERE butterfly_tenant_id = %d", $tenant_id ), ARRAY_A );
        } elseif ( $unit_id ) {
            $loft = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$lofts_table} WHERE butterfly_unit_id = %d", $unit_id ), ARRAY_A );
        } else {
            $loft = null;
        }

        if ( ! $loft ) {
            loft1325_log_action( 'butterflymx_sync', 'No loft mapping for keychain', array( 'payload' => $keychain ) );
            return false;
        }

        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bookings_table} WHERE butterfly_keychain_id = %d", $keychain_id ) );

        $check_in = isset( $keychain['starts_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $keychain['starts_at'] ) ) : null;
        $check_out = isset( $keychain['ends_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $keychain['ends_at'] ) ) : null;

        if ( ! $check_in || ! $check_out ) {
            return false;
        }

        $guest_name = isset( $keychain['name'] ) ? sanitize_text_field( $keychain['name'] ) : 'Invité';
        $status = 'tentative';

        $data = array(
            'external_ref' => 'butterflymx:' . $keychain_id,
            'loft_id' => absint( $loft['id'] ),
            'guest_name' => $guest_name,
            'guest_email' => isset( $keychain['email'] ) ? sanitize_email( $keychain['email'] ) : null,
            'guest_phone' => isset( $keychain['phone'] ) ? sanitize_text_field( $keychain['phone'] ) : null,
            'check_in_utc' => $check_in,
            'check_out_utc' => $check_out,
            'status' => $status,
            'butterfly_keychain_id' => $keychain_id,
            'updated_at' => current_time( 'mysql', 1 ),
        );

        if ( $existing ) {
            $wpdb->update( $bookings_table, $data, array( 'id' => $existing ) );
        } else {
            $data['created_at'] = current_time( 'mysql', 1 );
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $bookings_table, $data );
        }

        return true;
    }
}
