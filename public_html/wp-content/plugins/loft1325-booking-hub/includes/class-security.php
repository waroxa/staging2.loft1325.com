<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Security {
    public static function boot() {
        add_action( 'admin_post_loft1325_unlock', array( __CLASS__, 'handle_unlock' ) );
        add_action( 'admin_post_loft1325_update_password', array( __CLASS__, 'handle_password_update' ) );
    }

    public static function add_capabilities() {
        $role = get_role( 'administrator' );
        if ( $role ) {
            $role->add_cap( 'loft1325_manage_bookings' );
        }
    }

    public static function check_access() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        $unlock_until = get_user_meta( get_current_user_id(), LOFT1325_PASSWORD_META_KEY, true );
        if ( $unlock_until && intval( $unlock_until ) > time() ) {
            return true;
        }

        return false;
    }

    public static function render_lock_screen() {
        $action_url = admin_url( 'admin-post.php' );
        $nonce = wp_create_nonce( 'loft1325_unlock' );

        echo '<div class="loft1325-lock-screen">';
        echo '<h2>' . esc_html__( 'Entrer le mot de passe', 'loft1325-booking-hub' ) . '</h2>';
        echo '<p>' . esc_html__( 'Ce hub est protégé par un mot de passe configurable.', 'loft1325-booking-hub' ) . '</p>';
        echo '<form method="post" action="' . esc_url( $action_url ) . '">';
        echo '<input type="hidden" name="action" value="loft1325_unlock" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<input type="password" name="loft1325_password" placeholder="Mot de passe" required />';
        echo '<button type="submit" class="button button-primary loft1325-primary">' . esc_html__( 'Déverrouiller', 'loft1325-booking-hub' ) . '</button>';
        echo '</form>';
        echo '</div>';
    }

    public static function handle_unlock() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_unlock' );

        $password = isset( $_POST['loft1325_password'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_password'] ) ) : '';
        $settings = loft1325_get_settings();

        if ( ! wp_check_password( $password, $settings['password_hash'] ) ) {
            wp_safe_redirect( add_query_arg( 'loft1325_locked', '1', wp_get_referer() ) );
            exit;
        }

        update_user_meta( get_current_user_id(), LOFT1325_PASSWORD_META_KEY, time() + HOUR_IN_SECONDS * 12 );

        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    public static function handle_password_update() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_update_password' );

        $new_password = isset( $_POST['loft1325_new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_new_password'] ) ) : '';
        if ( strlen( $new_password ) < 6 ) {
            wp_safe_redirect( add_query_arg( 'loft1325_password_error', '1', wp_get_referer() ) );
            exit;
        }

        $settings = loft1325_get_settings();
        $settings['password_hash'] = wp_hash_password( $new_password );
        update_option( LOFT1325_SETTINGS_OPTION, $settings );

        wp_safe_redirect( add_query_arg( 'loft1325_password_updated', '1', wp_get_referer() ) );
        exit;
    }
}
