<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_DB {
    public static function boot() {
        if ( get_option( 'loft1325_booking_hub_db_version' ) !== LOFT1325_BOOKING_HUB_VERSION ) {
            self::create_tables();
            update_option( 'loft1325_booking_hub_db_version', LOFT1325_BOOKING_HUB_VERSION );
        }
    }

    public static function activate() {
        self::create_tables();
        Loft1325_Security::add_capabilities();
    }

    public static function create_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $log_table = $wpdb->prefix . 'loft1325_log';
        $cleaning_table = $wpdb->prefix . 'loft1325_cleaning_status';
        $maintenance_table = $wpdb->prefix . 'loft1325_maintenance_tasks';

        $sql = "CREATE TABLE {$lofts_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            loft_name VARCHAR(64) NOT NULL,
            loft_type ENUM('simple','double','penthouse') NOT NULL,
            butterfly_tenant_id BIGINT UNSIGNED NULL,
            butterfly_unit_id BIGINT UNSIGNED NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY loft_name (loft_name)
        ) {$charset_collate};

        CREATE TABLE {$bookings_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            external_ref VARCHAR(128) NULL,
            loft_id BIGINT UNSIGNED NOT NULL,
            guest_name VARCHAR(255) NOT NULL,
            guest_email VARCHAR(255) NULL,
            guest_phone VARCHAR(64) NULL,
            check_in_utc DATETIME NOT NULL,
            check_out_utc DATETIME NOT NULL,
            status ENUM('tentative','confirmed','checked_in','checked_out','cancelled') NOT NULL DEFAULT 'confirmed',
            notes TEXT NULL,
            butterfly_keychain_id BIGINT UNSIGNED NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY loft_id (loft_id),
            KEY status (status),
            KEY check_in_utc (check_in_utc),
            KEY check_out_utc (check_out_utc)
        ) {$charset_collate};

        CREATE TABLE {$log_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NULL,
            loft_id BIGINT UNSIGNED NULL,
            user_id BIGINT UNSIGNED NULL,
            action VARCHAR(64) NOT NULL,
            message TEXT NOT NULL,
            payload LONGTEXT NULL,
            response LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY loft_id (loft_id)
        ) {$charset_collate};

        CREATE TABLE {$cleaning_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            cleaning_status ENUM('pending','in_progress','ready','issue') NOT NULL DEFAULT 'pending',
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY booking_id (booking_id)
        ) {$charset_collate};

        CREATE TABLE {$maintenance_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            loft_label VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            details TEXT NULL,
            priority ENUM('critical','urgent','normal','low') NOT NULL DEFAULT 'normal',
            status ENUM('todo','in_progress','done') NOT NULL DEFAULT 'todo',
            assignee_email VARCHAR(255) NULL,
            requested_by_email VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY priority (priority)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    public static function seed_lofts( $lofts ) {
        global $wpdb;

        $table = $wpdb->prefix . 'loft1325_lofts';
        $now = current_time( 'mysql', 1 );

        foreach ( $lofts as $loft ) {
            $wpdb->insert(
                $table,
                array(
                    'loft_name' => sanitize_text_field( $loft['loft_name'] ),
                    'loft_type' => sanitize_text_field( $loft['loft_type'] ),
                    'butterfly_tenant_id' => isset( $loft['butterfly_tenant_id'] ) ? absint( $loft['butterfly_tenant_id'] ) : null,
                    'butterfly_unit_id' => isset( $loft['butterfly_unit_id'] ) ? absint( $loft['butterfly_unit_id'] ) : null,
                    'is_active' => isset( $loft['is_active'] ) ? absint( $loft['is_active'] ) : 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ),
                array( '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
            );
        }
    }
}
