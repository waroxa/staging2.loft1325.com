<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Security {
    public static function boot() {
        // Use native WordPress authentication only.
    }

    public static function add_capabilities() {
        $role = get_role( 'administrator' );
        if ( $role ) {
            $role->add_cap( 'loft1325_manage_bookings' );
        }
    }

    public static function check_access() {
        return current_user_can( 'loft1325_manage_bookings' );
    }
}
