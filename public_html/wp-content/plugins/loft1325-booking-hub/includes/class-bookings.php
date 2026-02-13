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

        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 120 );
        }

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
        $maps = self::get_sync_lookup_maps();

        $normalized_keychain = self::normalize_butterflymx_keychain( $keychain );

        $keychain_id = isset( $normalized_keychain['id'] ) ? absint( $normalized_keychain['id'] ) : 0;
        if ( ! $keychain_id ) {
            return false;
        }

        $tenant_id = isset( $normalized_keychain['tenant_id'] ) ? absint( $normalized_keychain['tenant_id'] ) : 0;
        $unit_id = isset( $normalized_keychain['unit_id'] ) ? absint( $normalized_keychain['unit_id'] ) : 0;

        $loft = self::resolve_loft_for_keychain( $normalized_keychain, $maps );

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
        $status = 'tentative';

        $data = array(
            'external_ref' => 'butterflymx:' . $keychain_id,
            'loft_id' => absint( $loft['id'] ),
            'guest_name' => $guest_name,
            'guest_email' => isset( $normalized_keychain['email'] ) ? sanitize_email( $normalized_keychain['email'] ) : null,
            'guest_phone' => isset( $normalized_keychain['phone'] ) ? sanitize_text_field( $normalized_keychain['phone'] ) : null,
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
            if ( ! empty( $wpdb->insert_id ) ) {
                $maps['booking_by_keychain'][ $keychain_id ] = (int) $wpdb->insert_id;
            }
        }

        return true;
    }

    private static function normalize_butterflymx_keychain( $keychain ) {
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

        return $normalized;
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
}
