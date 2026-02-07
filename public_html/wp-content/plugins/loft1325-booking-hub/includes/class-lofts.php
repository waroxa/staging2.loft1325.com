<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Lofts {
    public static function boot() {
        add_action( 'admin_post_loft1325_save_loft', array( __CLASS__, 'save_loft' ) );
        add_action( 'admin_post_loft1325_seed_lofts', array( __CLASS__, 'seed_default_lofts' ) );
    }

    public static function get_all() {
        global $wpdb;

        $table = $wpdb->prefix . 'loft1325_lofts';

        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY loft_name ASC", ARRAY_A );
    }

    public static function get_available_by_type( $type, $check_in, $check_out ) {
        global $wpdb;

        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';

        $query = $wpdb->prepare(
            "SELECT l.* FROM {$lofts_table} l
            WHERE l.is_active = 1
            AND l.loft_type = %s
            AND l.id NOT IN (
                SELECT b.loft_id FROM {$bookings_table} b
                WHERE b.status IN ('confirmed','checked_in')
                AND %s < b.check_out_utc
                AND %s > b.check_in_utc
            )
            ORDER BY l.loft_name ASC",
            $type,
            $check_in,
            $check_out
        );

        return $wpdb->get_results( $query, ARRAY_A );
    }

    public static function save_loft() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_save_loft' );

        global $wpdb;
        $table = $wpdb->prefix . 'loft1325_lofts';

        $id = isset( $_POST['loft_id'] ) ? absint( $_POST['loft_id'] ) : 0;
        $data = array(
            'loft_name' => sanitize_text_field( wp_unslash( $_POST['loft_name'] ) ),
            'loft_type' => sanitize_text_field( wp_unslash( $_POST['loft_type'] ) ),
            'butterfly_tenant_id' => isset( $_POST['butterfly_tenant_id'] ) ? absint( $_POST['butterfly_tenant_id'] ) : null,
            'butterfly_unit_id' => isset( $_POST['butterfly_unit_id'] ) ? absint( $_POST['butterfly_unit_id'] ) : null,
            'is_active' => isset( $_POST['is_active'] ) ? 1 : 0,
            'updated_at' => current_time( 'mysql', 1 ),
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $data['created_at'] = current_time( 'mysql', 1 );
            $wpdb->insert( $table, $data );
        }

        wp_safe_redirect( add_query_arg( 'loft1325_saved', '1', wp_get_referer() ) );
        exit;
    }

    public static function seed_default_lofts() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_seed_lofts' );

        $lofts = array();
        for ( $i = 1; $i <= 22; $i++ ) {
            $type = 'simple';
            if ( $i > 8 && $i <= 16 ) {
                $type = 'double';
            }
            if ( $i > 16 ) {
                $type = 'penthouse';
            }
            $lofts[] = array(
                'loft_name' => 'LOFT ' . ( 200 + $i ),
                'loft_type' => $type,
            );
        }

        Loft1325_DB::seed_lofts( $lofts );

        wp_safe_redirect( add_query_arg( 'loft1325_seeded', '1', wp_get_referer() ) );
        exit;
    }
}
