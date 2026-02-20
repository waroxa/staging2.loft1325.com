<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Bookings {
    public static function boot() {
        add_action( 'admin_post_loft1325_create_booking', array( __CLASS__, 'create_booking' ) );
        add_action( 'admin_post_loft1325_create_key', array( __CLASS__, 'create_key' ) );
        add_action( 'admin_post_loft1325_revoke_key', array( __CLASS__, 'revoke_key' ) );
        add_action( 'admin_post_loft1325_sync_keychains', array( __CLASS__, 'sync_from_butterflymx' ) );
        add_action( 'loft1325_booking_hub_sync_keychains', array( __CLASS__, 'run_scheduled_sync' ) );
        add_action( 'init', array( __CLASS__, 'ensure_sync_schedule' ) );
    }

    public static function create_key() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_create_key' );

        $booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
        if ( ! $booking_id ) {
            wp_safe_redirect( add_query_arg( 'loft1325_key_error', '1', wp_get_referer() ) );
            exit;
        }

        self::create_key_for_booking( $booking_id );

        wp_safe_redirect( add_query_arg( 'loft1325_key_created', '1', wp_get_referer() ) );
        exit;
    }

    public static function ensure_sync_schedule() {
        $hook  = 'loft1325_booking_hub_sync_keychains';
        $event = wp_get_scheduled_event( $hook );

        if ( ! $event ) {
            wp_schedule_event( time(), 'loft1325_every_15_minutes', $hook );
            return;
        }

        if ( 'loft1325_every_15_minutes' !== $event->schedule ) {
            wp_clear_scheduled_hook( $hook );
            wp_schedule_event( time(), 'loft1325_every_15_minutes', $hook );
        }
    }

    public static function run_scheduled_sync() {
        self::sync_from_butterflymx( true );
    }

    public static function get_dashboard_counts() {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $today = gmdate( 'Y-m-d' );

        $checkins = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookings_table} WHERE DATE(check_in_utc) = %s AND status IN ('tentative','confirmed','checked_in')",
            $today
        ) );

        $checkouts = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookings_table} WHERE DATE(check_out_utc) = %s AND status IN ('tentative','confirmed','checked_in')",
            $today
        ) );

        $occupied = $wpdb->get_var( "SELECT COUNT(*) FROM {$bookings_table} WHERE status IN ('tentative','confirmed','checked_in')" );
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

    public static function get_booking( $booking_id ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $query = $wpdb->prepare(
            "SELECT b.*, l.loft_name, l.loft_type
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            WHERE b.id = %d
            LIMIT 1",
            $booking_id
        );

        return $wpdb->get_row( $query, ARRAY_A );
    }

    public static function get_review_bookings( $limit = 100 ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $query = $wpdb->prepare(
            "SELECT b.*, l.loft_name, l.loft_type
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            WHERE b.status = 'tentative'
            AND b.butterfly_keychain_id IS NOT NULL
            AND b.check_out_utc >= %s
            ORDER BY b.check_in_utc ASC
            LIMIT %d",
            gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
            $limit
        );

        return $wpdb->get_results( $query, ARRAY_A );
    }

    public static function get_approved_bookings_for_range( $start_utc, $end_utc, $loft_id = 0 ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $query = "SELECT b.*, l.loft_name, l.loft_type
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            WHERE b.status IN ('confirmed','checked_in','checked_out')
            AND %s < b.check_out_utc
            AND %s > b.check_in_utc";

        $args = array( $start_utc, $end_utc );
        if ( $loft_id > 0 ) {
            $query .= ' AND b.loft_id = %d';
            $args[] = $loft_id;
        }

        $query .= ' ORDER BY b.loft_id ASC, b.check_in_utc ASC';

        return $wpdb->get_results( $wpdb->prepare( $query, $args ), ARRAY_A );
    }

    public static function get_clients( $limit = 500 ) {
        global $wpdb;

        $clients_table = $wpdb->prefix . 'loft1325_clients';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, full_name, email, phone, last_booking_at
                FROM {$clients_table}
                ORDER BY COALESCE(last_booking_at, created_at) DESC
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
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
        $booking_source = isset( $_POST['booking_source'] ) ? sanitize_key( wp_unslash( $_POST['booking_source'] ) ) : 'admin';
        if ( ! in_array( $booking_source, array( 'website', 'airbnb', 'admin' ), true ) ) {
            $booking_source = 'admin';
        }

        $identity_type = isset( $_POST['identity_type'] ) ? sanitize_text_field( wp_unslash( $_POST['identity_type'] ) ) : '';
        $identity_number = isset( $_POST['identity_number'] ) ? sanitize_text_field( wp_unslash( $_POST['identity_number'] ) ) : '';
        $coupon_code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

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
            AND status IN ('tentative','confirmed','checked_in')
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
            'booking_source' => $booking_source,
            'coupon_code' => $coupon_code,
            'identity_type' => $identity_type,
            'identity_number' => $identity_number,
            'check_in_utc' => $check_in,
            'check_out_utc' => $check_out,
            'status' => 'confirmed',
            'notes' => sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ),
            'created_by' => get_current_user_id(),
            'created_at' => current_time( 'mysql', 1 ),
            'updated_at' => current_time( 'mysql', 1 ),
        );

        $inserted = $wpdb->insert( $bookings_table, $data );
        $booking_id = $wpdb->insert_id;

        if ( false === $inserted || $booking_id <= 0 ) {
            $wpdb->query( $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $lock_key ) );

            loft1325_log_action(
                'booking_create_error',
                'Booking insert failed',
                array(
                    'loft_id' => $loft_id,
                    'payload' => array(
                        'wpdb_error' => $wpdb->last_error,
                        'guest_email' => $data['guest_email'],
                        'check_in_utc' => $data['check_in_utc'],
                        'check_out_utc' => $data['check_out_utc'],
                        'booking_source' => $data['booking_source'],
                    ),
                )
            );

            wp_safe_redirect( add_query_arg( 'loft1325_error', 'create_failed', wp_get_referer() ) );
            exit;
        }

        if ( $booking_id > 0 ) {
            $identity_front_media_id = self::upload_identity_media( 'identity_front', $booking_id );
            $identity_back_media_id = self::upload_identity_media( 'identity_back', $booking_id );

            if ( $identity_front_media_id || $identity_back_media_id ) {
                $wpdb->update(
                    $bookings_table,
                    array(
                        'identity_front_media_id' => $identity_front_media_id ? $identity_front_media_id : null,
                        'identity_back_media_id' => $identity_back_media_id ? $identity_back_media_id : null,
                        'updated_at' => current_time( 'mysql', 1 ),
                    ),
                    array( 'id' => $booking_id ),
                    array( '%d', '%d', '%s' ),
                    array( '%d' )
                );
            }
        }

        self::upsert_client_record( $data['guest_name'], $data['guest_email'], $data['guest_phone'], array(
            'source' => 'manual_booking_form',
            'booking_id' => $booking_id,
        ) );

        $wpdb->query( $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $lock_key ) );

        loft1325_log_action( 'booking_created', 'Booking created', array( 'booking_id' => $booking_id, 'loft_id' => $loft_id ) );

        $key_creation_failed = false;
        if ( isset( $_POST['create_key'] ) ) {
            $key_creation_failed = ! self::create_key_for_booking( $booking_id );
        }

        $redirect_args = array( 'loft1325_created' => '1' );
        if ( $key_creation_failed ) {
            $redirect_args['loft1325_key_error'] = '1';
        }

        wp_safe_redirect( add_query_arg( $redirect_args, wp_get_referer() ) );
        exit;
    }

    private static function upload_identity_media( $file_key, $booking_id ) {
        if ( empty( $_FILES[ $file_key ]['name'] ) ) {
            return 0;
        }

        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attachment_id = media_handle_upload( $file_key, 0 );
        if ( is_wp_error( $attachment_id ) ) {
            loft1325_log_action( 'identity_upload_error', 'Identity document upload failed', array( 'booking_id' => $booking_id, 'field' => $file_key, 'error' => $attachment_id->get_error_message() ) );
            return 0;
        }

        update_post_meta( $attachment_id, '_loft1325_booking_id', $booking_id );
        update_post_meta( $attachment_id, '_loft1325_identity_document_slot', $file_key );

        return (int) $attachment_id;
    }

    public static function create_key_for_booking( $booking_id ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';

        $booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bookings_table} WHERE id = %d", $booking_id ), ARRAY_A );
        if ( ! $booking ) {
            return false;
        }

        $loft = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$lofts_table} WHERE id = %d", $booking['loft_id'] ), ARRAY_A );
        if ( ! $loft ) {
            return false;
        }

        $settings = loft1325_get_settings();
        $tenant_id = $loft['butterfly_tenant_id'] ? absint( $loft['butterfly_tenant_id'] ) : null;
        $unit_id = $loft['butterfly_unit_id'] ? absint( $loft['butterfly_unit_id'] ) : null;

        if ( ( $tenant_id && $unit_id ) || ( ! $tenant_id && ! $unit_id ) ) {
            loft1325_log_action( 'butterflymx_error', 'Invalid tenant/unit mapping', array( 'booking_id' => $booking_id, 'loft_id' => $loft['id'] ) );
            return false;
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
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['id'] ) ) {
            $wpdb->update( $bookings_table, array( 'butterfly_keychain_id' => absint( $body['id'] ) ), array( 'id' => $booking_id ) );
            return true;
        }

        return false;
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

    public static function sync_from_butterflymx( $cron = false ) {
        if ( ! $cron ) {
            if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
                wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
            }

            check_admin_referer( 'loft1325_sync_keychains' );
        }

        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 120 );
        }

        $synced_count = self::sync_keychains_from_api();

        if ( is_wp_error( $synced_count ) ) {
            if ( $cron ) {
                return;
            }

            wp_safe_redirect( add_query_arg( 'loft1325_sync_error', '1', wp_get_referer() ) );
            exit;
        }

        if ( $cron ) {
            return;
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

    private static function sync_keychains_from_api() {
        $keychain_response = Loft1325_API_ButterflyMX::list_keychains_paginated();
        if ( is_wp_error( $keychain_response ) ) {
            return $keychain_response;
        }

        $keychains = isset( $keychain_response['data'] ) && is_array( $keychain_response['data'] ) ? $keychain_response['data'] : array();
        $virtual_keys_response = Loft1325_API_ButterflyMX::list_virtual_keys_paginated();
        $tenants_response = Loft1325_API_ButterflyMX::list_tenants_paginated();
        $units_response = Loft1325_API_ButterflyMX::list_units_paginated();

        $virtual_keys = ( ! is_wp_error( $virtual_keys_response ) && isset( $virtual_keys_response['data'] ) && is_array( $virtual_keys_response['data'] ) ) ? $virtual_keys_response['data'] : array();
        $tenants = ( ! is_wp_error( $tenants_response ) && isset( $tenants_response['data'] ) && is_array( $tenants_response['data'] ) ) ? $tenants_response['data'] : array();
        $units = ( ! is_wp_error( $units_response ) && isset( $units_response['data'] ) && is_array( $units_response['data'] ) ) ? $units_response['data'] : array();

        $context = self::build_keychain_context( $virtual_keys, $tenants, $units );

        $synced_count = 0;
        $valid_keychain_ids = array();

        foreach ( $keychains as $keychain ) {
            $normalized_keychain = self::normalize_butterflymx_keychain( $keychain, $context );

            if ( ! self::is_syncable_keychain( $normalized_keychain ) ) {
                continue;
            }

            $keychain_id = isset( $normalized_keychain['id'] ) ? absint( $normalized_keychain['id'] ) : 0;
            if ( $keychain_id ) {
                $valid_keychain_ids[] = $keychain_id;
            }

            if ( self::upsert_booking_from_keychain( $normalized_keychain, $context ) ) {
                $synced_count++;
            }
        }

        self::clear_stale_keychain_links( $valid_keychain_ids );

        return $synced_count;
    }

    /**
     * Determine whether a ButterflyMX keychain should be treated as active/valid in sync.
     *
     * @param array $keychain Normalized keychain payload.
     *
     * @return bool
     */
    private static function is_syncable_keychain( $keychain ) {
        $attributes = isset( $keychain['attributes'] ) && is_array( $keychain['attributes'] ) ? $keychain['attributes'] : array();

        $string_flags = array();

        foreach ( array( 'status', 'state', 'validity', 'key_status' ) as $field ) {
            if ( isset( $keychain[ $field ] ) ) {
                $string_flags[] = (string) $keychain[ $field ];
            }

            if ( isset( $attributes[ $field ] ) ) {
                $string_flags[] = (string) $attributes[ $field ];
            }
        }

        foreach ( $string_flags as $value ) {
            $normalized = strtolower( trim( str_replace( array( '-', ' ' ), '_', (string) $value ) ) );

            if ( in_array( $normalized, array( 'not_valid', 'invalid', 'inactive', 'revoked', 'deactivated', 'deleted', 'expired' ), true ) ) {
                return false;
            }
        }

        foreach ( array( 'is_valid', 'active', 'enabled' ) as $field ) {
            if ( array_key_exists( $field, $keychain ) && false === filter_var( $keychain[ $field ], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) {
                return false;
            }

            if ( array_key_exists( $field, $attributes ) && false === filter_var( $attributes[ $field ], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) {
                return false;
            }
        }

        foreach ( array( 'deactivated_at', 'revoked_at', 'deleted_at', 'invalidated_at' ) as $timestamp_field ) {
            if ( ! empty( $keychain[ $timestamp_field ] ) || ! empty( $attributes[ $timestamp_field ] ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove keychain links from bookings that no longer map to valid keychains in ButterflyMX.
     *
     * @param array $valid_keychain_ids Keychain IDs considered valid after filtering.
     *
     * @return void
     */
    private static function clear_stale_keychain_links( $valid_keychain_ids ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $active_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $valid_keychain_ids ) ) ) );

        if ( empty( $active_ids ) ) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$bookings_table}
                    SET butterfly_keychain_id = NULL, updated_at = %s
                    WHERE butterfly_keychain_id IS NOT NULL
                      AND external_ref LIKE %s",
                    current_time( 'mysql', 1 ),
                    'butterflymx:%'
                )
            );

            return;
        }

        $placeholders = implode( ',', array_fill( 0, count( $active_ids ), '%d' ) );

        $stale_bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, butterfly_keychain_id FROM {$bookings_table}
                WHERE butterfly_keychain_id IS NOT NULL
                  AND external_ref LIKE %s
                  AND butterfly_keychain_id NOT IN ({$placeholders})",
                array_merge( array( 'butterflymx:%' ), $active_ids )
            ),
            ARRAY_A
        );

        foreach ( (array) $stale_bookings as $booking ) {
            $booking_id = isset( $booking['id'] ) ? absint( $booking['id'] ) : 0;
            if ( ! $booking_id ) {
                continue;
            }

            $wpdb->update(
                $bookings_table,
                array(
                    'butterfly_keychain_id' => null,
                    'updated_at' => current_time( 'mysql', 1 ),
                ),
                array( 'id' => $booking_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
        }
    }

    private static function upsert_booking_from_keychain( $keychain, $context = array() ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $maps = self::get_sync_lookup_maps();

        $normalized_keychain = self::normalize_butterflymx_keychain( $keychain, $context );

        $keychain_id = isset( $normalized_keychain['id'] ) ? absint( $normalized_keychain['id'] ) : 0;
        if ( ! $keychain_id ) {
            return false;
        }

        $tenant_id = isset( $normalized_keychain['tenant_id'] ) ? absint( $normalized_keychain['tenant_id'] ) : 0;
        $unit_id = isset( $normalized_keychain['unit_id'] ) ? absint( $normalized_keychain['unit_id'] ) : 0;

        $loft = self::resolve_loft_for_keychain( $normalized_keychain, $maps );

        if ( ! $loft && ! empty( $normalized_keychain['name'] ) ) {
            $normalized_name = strtoupper( preg_replace( '/[^A-Z0-9]/', '', (string) $normalized_keychain['name'] ) );

            if ( preg_match( '/LOFT\s*([0-9]{2,4})/i', (string) $normalized_keychain['name'], $matches ) ) {
                $needle = 'LOFT' . $matches[1];
                $loft = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$lofts_table} WHERE REPLACE(UPPER(loft_name), ' ', '') LIKE %s LIMIT 1",
                        '%' . $wpdb->esc_like( $needle ) . '%'
                    ),
                    ARRAY_A
                );
            } elseif ( '' !== $normalized_name ) {
                $loft = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$lofts_table} WHERE REPLACE(UPPER(loft_name), ' ', '') LIKE %s LIMIT 1",
                        '%' . $wpdb->esc_like( $normalized_name ) . '%'
                    ),
                    ARRAY_A
                );
            }
        }

        if ( ! $loft ) {
            loft1325_log_action( 'butterflymx_sync', 'No loft mapping for keychain', array( 'payload' => $normalized_keychain ) );
            return false;
        }

        $existing = isset( $maps['booking_by_keychain'][ $keychain_id ] ) ? absint( $maps['booking_by_keychain'][ $keychain_id ] ) : 0;

        $check_in = isset( $normalized_keychain['starts_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $normalized_keychain['starts_at'] ) ) : null;
        $check_out = isset( $normalized_keychain['ends_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $normalized_keychain['ends_at'] ) ) : null;

        if ( ! $check_in || ! $check_out ) {
            return false;
        }

        $guest_name = isset( $normalized_keychain['name'] ) ? sanitize_text_field( $normalized_keychain['name'] ) : 'Invité';
        $guest_email = isset( $normalized_keychain['email'] ) ? sanitize_email( $normalized_keychain['email'] ) : null;
        $guest_phone = isset( $normalized_keychain['phone'] ) ? sanitize_text_field( $normalized_keychain['phone'] ) : null;
        $existing_booking = null;
        if ( $existing ) {
            $existing_booking = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$bookings_table} WHERE id = %d", $existing ), ARRAY_A );
        }

        if ( ! $existing && strtotime( $check_out ) < time() ) {
            return false;
        }

        $status = 'tentative';
        if ( ! empty( $existing_booking['status'] ) ) {
            $status = sanitize_key( $existing_booking['status'] );
        }

        $data = array(
            'external_ref' => 'butterflymx:' . $keychain_id,
            'loft_id' => absint( $loft['id'] ),
            'guest_name' => $guest_name,
            'guest_email' => $guest_email,
            'guest_phone' => $guest_phone,
            'check_in_utc' => $check_in,
            'check_out_utc' => $check_out,
            'status' => $status,
            'butterfly_keychain_id' => $keychain_id,
            'updated_at' => current_time( 'mysql', 1 ),
        );

        if ( $existing ) {
            $wpdb->update( $bookings_table, $data, array( 'id' => $existing ) );
            self::upsert_client_record( $guest_name, $guest_email, $guest_phone, array(
                'source' => 'butterflymx_sync',
                'booking_id' => $existing,
            ) );
        } else {
            $data['created_at'] = current_time( 'mysql', 1 );
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $bookings_table, $data );
            if ( ! empty( $wpdb->insert_id ) ) {
                $maps['booking_by_keychain'][ $keychain_id ] = (int) $wpdb->insert_id;
                self::upsert_client_record( $guest_name, $guest_email, $guest_phone, array(
                    'source' => 'butterflymx_sync',
                    'booking_id' => (int) $wpdb->insert_id,
                ) );
            }
        }

        return true;
    }

    private static function normalize_butterflymx_keychain( $keychain, $context = array() ) {
        $normalized = is_array( $keychain ) ? $keychain : array();
        $attributes = isset( $normalized['attributes'] ) && is_array( $normalized['attributes'] ) ? $normalized['attributes'] : array();
        $relationships = isset( $normalized['relationships'] ) && is_array( $normalized['relationships'] ) ? $normalized['relationships'] : array();

        if ( empty( $normalized['tenant_id'] ) && isset( $relationships['tenant']['data']['id'] ) ) {
            $normalized['tenant_id'] = absint( $relationships['tenant']['data']['id'] );
        }

        if ( empty( $normalized['unit_id'] ) && isset( $relationships['unit']['data']['id'] ) ) {
            $normalized['unit_id'] = absint( $relationships['unit']['data']['id'] );
        }

        if ( empty( $normalized['unit_id'] ) && isset( $relationships['devices']['data'] ) && is_array( $relationships['devices']['data'] ) ) {
            foreach ( $relationships['devices']['data'] as $device ) {
                if ( isset( $device['type'], $device['id'] ) && 'panels' === $device['type'] ) {
                    $normalized['unit_id'] = absint( $device['id'] );
                    break;
                }
            }
        }

        if ( empty( $normalized['starts_at'] ) && ! empty( $attributes['starts_at'] ) ) {
            $normalized['starts_at'] = $attributes['starts_at'];
        }

        if ( empty( $normalized['ends_at'] ) && ! empty( $attributes['ends_at'] ) ) {
            $normalized['ends_at'] = $attributes['ends_at'];
        }

        if ( empty( $normalized['name'] ) && ! empty( $attributes['name'] ) ) {
            $normalized['name'] = $attributes['name'];
        }

        if ( ( empty( $normalized['tenant_id'] ) || empty( $normalized['unit_id'] ) ) && ! empty( $normalized['virtual_key_ids'] ) && is_array( $normalized['virtual_key_ids'] ) ) {
            foreach ( $normalized['virtual_key_ids'] as $virtual_key_id ) {
                $virtual_key_id = absint( $virtual_key_id );
                if ( ! $virtual_key_id || empty( $context['virtual_keys'][ $virtual_key_id ] ) ) {
                    continue;
                }

                $virtual_key = $context['virtual_keys'][ $virtual_key_id ];
                if ( empty( $normalized['tenant_id'] ) && ! empty( $virtual_key['tenant_id'] ) ) {
                    $normalized['tenant_id'] = absint( $virtual_key['tenant_id'] );
                }
                if ( empty( $normalized['unit_id'] ) && ! empty( $virtual_key['unit_id'] ) ) {
                    $normalized['unit_id'] = absint( $virtual_key['unit_id'] );
                }
            }
        }

        if ( empty( $normalized['tenant_id'] ) && ! empty( $normalized['unit_id'] ) && isset( $context['tenant_by_unit'][ (int) $normalized['unit_id'] ] ) ) {
            $normalized['tenant_id'] = (int) $context['tenant_by_unit'][ (int) $normalized['unit_id'] ];
        }

        if ( empty( $normalized['unit_id'] ) && ! empty( $normalized['tenant_id'] ) && isset( $context['unit_by_tenant'][ (int) $normalized['tenant_id'] ] ) ) {
            $normalized['unit_id'] = (int) $context['unit_by_tenant'][ (int) $normalized['tenant_id'] ];
        }

        return $normalized;
    }

    private static function build_keychain_context( $virtual_keys, $tenants, $units ) {
        $context = array(
            'virtual_keys' => array(),
            'tenant_by_unit' => array(),
            'unit_by_tenant' => array(),
        );

        foreach ( (array) $virtual_keys as $virtual_key ) {
            $virtual_key_id = isset( $virtual_key['id'] ) ? absint( $virtual_key['id'] ) : 0;
            if ( ! $virtual_key_id ) {
                continue;
            }

            $attributes = isset( $virtual_key['attributes'] ) && is_array( $virtual_key['attributes'] ) ? $virtual_key['attributes'] : array();
            $relationships = isset( $virtual_key['relationships'] ) && is_array( $virtual_key['relationships'] ) ? $virtual_key['relationships'] : array();

            $context['virtual_keys'][ $virtual_key_id ] = array(
                'tenant_id' => isset( $virtual_key['tenant_id'] ) ? absint( $virtual_key['tenant_id'] ) : absint( $attributes['tenant_id'] ?? ( $relationships['tenant']['data']['id'] ?? 0 ) ),
                'unit_id' => isset( $virtual_key['unit_id'] ) ? absint( $virtual_key['unit_id'] ) : absint( $attributes['unit_id'] ?? ( $relationships['unit']['data']['id'] ?? 0 ) ),
            );
        }

        foreach ( (array) $tenants as $tenant ) {
            $tenant_id = isset( $tenant['id'] ) ? absint( $tenant['id'] ) : 0;
            if ( ! $tenant_id ) {
                continue;
            }

            $tenant_attributes = isset( $tenant['attributes'] ) && is_array( $tenant['attributes'] ) ? $tenant['attributes'] : array();
            $tenant_relationships = isset( $tenant['relationships'] ) && is_array( $tenant['relationships'] ) ? $tenant['relationships'] : array();
            $unit_id = absint( $tenant['unit_id'] ?? $tenant_attributes['unit_id'] ?? ( $tenant_relationships['unit']['data']['id'] ?? 0 ) );

            if ( $unit_id ) {
                $context['unit_by_tenant'][ $tenant_id ] = $unit_id;
                $context['tenant_by_unit'][ $unit_id ] = $tenant_id;
            }
        }

        foreach ( (array) $units as $unit ) {
            $unit_id = isset( $unit['id'] ) ? absint( $unit['id'] ) : 0;
            if ( ! $unit_id || isset( $context['tenant_by_unit'][ $unit_id ] ) ) {
                continue;
            }

            $unit_attributes = isset( $unit['attributes'] ) && is_array( $unit['attributes'] ) ? $unit['attributes'] : array();
            $unit_relationships = isset( $unit['relationships'] ) && is_array( $unit['relationships'] ) ? $unit['relationships'] : array();
            $tenant_id = absint( $unit['tenant_id'] ?? $unit_attributes['tenant_id'] ?? ( $unit_relationships['tenant']['data']['id'] ?? 0 ) );

            if ( $tenant_id ) {
                $context['tenant_by_unit'][ $unit_id ] = $tenant_id;
                $context['unit_by_tenant'][ $tenant_id ] = $unit_id;
            }
        }

        return $context;
    }

    private static function resolve_loft_for_keychain( $normalized_keychain, $maps ) {
        $tenant_id = isset( $normalized_keychain['tenant_id'] ) ? absint( $normalized_keychain['tenant_id'] ) : 0;
        $unit_id = isset( $normalized_keychain['unit_id'] ) ? absint( $normalized_keychain['unit_id'] ) : 0;

        if ( $tenant_id && isset( $maps['loft_by_tenant'][ $tenant_id ] ) ) {
            return $maps['loft_by_tenant'][ $tenant_id ];
        }

        if ( $unit_id && isset( $maps['loft_by_unit'][ $unit_id ] ) ) {
            return $maps['loft_by_unit'][ $unit_id ];
        }

        if ( $tenant_id && isset( $maps['legacy_unit_label_by_tenant'][ $tenant_id ] ) ) {
            $legacy_label = $maps['legacy_unit_label_by_tenant'][ $tenant_id ];
            if ( isset( $maps['loft_by_name_normalized'][ $legacy_label ] ) ) {
                return $maps['loft_by_name_normalized'][ $legacy_label ];
            }
        }

        if ( $unit_id && isset( $maps['legacy_unit_name_by_api_id'][ $unit_id ] ) ) {
            $legacy_name = $maps['legacy_unit_name_by_api_id'][ $unit_id ];
            if ( isset( $maps['loft_by_name_normalized'][ $legacy_name ] ) ) {
                return $maps['loft_by_name_normalized'][ $legacy_name ];
            }
        }

        $name = isset( $normalized_keychain['name'] ) ? (string) $normalized_keychain['name'] : '';
        if ( '' !== $name ) {
            $label = self::normalize_loft_label( $name );
            if ( isset( $maps['loft_by_name_normalized'][ $label ] ) ) {
                return $maps['loft_by_name_normalized'][ $label ];
            }

            if ( preg_match( '/LOFT\s*([0-9]{2,4})/i', $name, $matches ) ) {
                $needle = 'LOFT' . $matches[1];
                if ( isset( $maps['loft_by_name_normalized'][ $needle ] ) ) {
                    return $maps['loft_by_name_normalized'][ $needle ];
                }
            }
        }

        return null;
    }

    private static function get_sync_lookup_maps() {
        static $maps = null;

        if ( null !== $maps ) {
            return $maps;
        }

        global $wpdb;

        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $legacy_tenants_table = $wpdb->prefix . 'loft_tenants';
        $legacy_units_table = $wpdb->prefix . 'loft_units';

        $maps = array(
            'loft_by_tenant' => array(),
            'loft_by_unit' => array(),
            'loft_by_name_normalized' => array(),
            'legacy_unit_label_by_tenant' => array(),
            'legacy_unit_name_by_api_id' => array(),
            'booking_by_keychain' => array(),
        );

        $lofts = $wpdb->get_results( "SELECT * FROM {$lofts_table}", ARRAY_A );
        foreach ( (array) $lofts as $loft ) {
            $loft_id = isset( $loft['id'] ) ? absint( $loft['id'] ) : 0;
            if ( ! $loft_id ) {
                continue;
            }

            $tenant_id = isset( $loft['butterfly_tenant_id'] ) ? absint( $loft['butterfly_tenant_id'] ) : 0;
            $unit_id = isset( $loft['butterfly_unit_id'] ) ? absint( $loft['butterfly_unit_id'] ) : 0;
            $normalized_name = self::normalize_loft_label( (string) ( $loft['loft_name'] ?? '' ) );

            if ( $tenant_id ) {
                $maps['loft_by_tenant'][ $tenant_id ] = $loft;
            }
            if ( $unit_id ) {
                $maps['loft_by_unit'][ $unit_id ] = $loft;
            }
            if ( '' !== $normalized_name ) {
                $maps['loft_by_name_normalized'][ $normalized_name ] = $loft;
            }
        }

        $bookings = $wpdb->get_results( "SELECT id, butterfly_keychain_id FROM {$bookings_table} WHERE butterfly_keychain_id IS NOT NULL", ARRAY_A );
        foreach ( (array) $bookings as $booking ) {
            $keychain_id = isset( $booking['butterfly_keychain_id'] ) ? absint( $booking['butterfly_keychain_id'] ) : 0;
            $booking_id = isset( $booking['id'] ) ? absint( $booking['id'] ) : 0;
            if ( $keychain_id && $booking_id ) {
                $maps['booking_by_keychain'][ $keychain_id ] = $booking_id;
            }
        }

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_tenants_table ) ) === $legacy_tenants_table ) {
            $legacy_tenants = $wpdb->get_results( "SELECT tenant_id, unit_label FROM {$legacy_tenants_table}", ARRAY_A );
            foreach ( (array) $legacy_tenants as $tenant ) {
                $tenant_id = isset( $tenant['tenant_id'] ) ? absint( $tenant['tenant_id'] ) : 0;
                $unit_label = self::normalize_loft_label( (string) ( $tenant['unit_label'] ?? '' ) );
                if ( $tenant_id && '' !== $unit_label ) {
                    $maps['legacy_unit_label_by_tenant'][ $tenant_id ] = $unit_label;
                }
            }
        }

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_units_table ) ) === $legacy_units_table ) {
            $legacy_units = $wpdb->get_results( "SELECT unit_id_api, unit_name FROM {$legacy_units_table}", ARRAY_A );
            foreach ( (array) $legacy_units as $unit ) {
                $api_id = isset( $unit['unit_id_api'] ) ? absint( $unit['unit_id_api'] ) : 0;
                $unit_name = self::normalize_loft_label( (string) ( $unit['unit_name'] ?? '' ) );
                if ( $api_id && '' !== $unit_name ) {
                    $maps['legacy_unit_name_by_api_id'][ $api_id ] = $unit_name;
                }
            }
        }

        return $maps;
    }

    private static function normalize_loft_label( $value ) {
        $value = strtoupper( trim( (string) $value ) );
        return preg_replace( '/[^A-Z0-9]/', '', $value );
    }

    private static function upsert_client_record( $full_name, $email, $phone = '', $meta = array() ) {
        global $wpdb;

        $email = sanitize_email( (string) $email );
        if ( ! is_email( $email ) ) {
            return;
        }

        $clients_table = $wpdb->prefix . 'loft1325_clients';
        $now = current_time( 'mysql', 1 );

        $existing = $wpdb->get_row(
            $wpdb->prepare( "SELECT id, meta, first_booking_at FROM {$clients_table} WHERE email = %s LIMIT 1", $email ),
            ARRAY_A
        );

        $merged_meta = array();
        if ( ! empty( $existing['meta'] ) ) {
            $decoded_meta = json_decode( $existing['meta'], true );
            if ( is_array( $decoded_meta ) ) {
                $merged_meta = $decoded_meta;
            }
        }

        if ( is_array( $meta ) ) {
            $merged_meta = array_merge( $merged_meta, $meta );
        }

        $payload = array(
            'full_name' => sanitize_text_field( (string) $full_name ),
            'email' => $email,
            'phone' => sanitize_text_field( (string) $phone ),
            'meta' => wp_json_encode( $merged_meta ),
            'last_booking_at' => $now,
            'updated_at' => $now,
        );

        if ( $existing ) {
            $wpdb->update(
                $clients_table,
                $payload,
                array( 'id' => absint( $existing['id'] ) ),
                array( '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
            return;
        }

        $payload['created_at'] = $now;
        $payload['first_booking_at'] = $now;

        $wpdb->insert(
            $clients_table,
            $payload,
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
    }
}
