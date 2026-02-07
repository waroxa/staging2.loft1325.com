<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Frontend_Pages {
    public static function boot() {
        add_shortcode( 'loft1325_booking_hub', array( __CLASS__, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'init', array( __CLASS__, 'handle_unlock' ) );
    }

    public static function enqueue_assets() {
        if ( ! self::is_shortcode_present() ) {
            return;
        }

        wp_enqueue_style( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.css', array(), LOFT1325_BOOKING_HUB_VERSION );
        wp_enqueue_script( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.js', array( 'jquery' ), LOFT1325_BOOKING_HUB_VERSION, true );
    }

    private static function is_shortcode_present() {
        if ( ! is_singular() ) {
            return false;
        }

        global $post;
        if ( ! $post ) {
            return false;
        }

        return has_shortcode( $post->post_content, 'loft1325_booking_hub' );
    }

    public static function handle_unlock() {
        if ( empty( $_POST['loft1325_frontend_unlock'] ) ) {
            return;
        }

        $password = isset( $_POST['loft1325_password'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_password'] ) ) : '';
        $settings = loft1325_get_settings();

        if ( ! wp_check_password( $password, $settings['password_hash'] ) ) {
            set_transient( 'loft1325_frontend_error', true, MINUTE_IN_SECONDS );
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        setcookie( 'loft1325_hub_unlocked', '1', time() + HOUR_IN_SECONDS * 12, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    private static function is_unlocked() {
        return isset( $_COOKIE['loft1325_hub_unlocked'] ) && '1' === $_COOKIE['loft1325_hub_unlocked'];
    }

    public static function render_shortcode() {
        ob_start();

        echo '<div class="loft1325-admin loft1325-frontend">';

        if ( ! self::is_unlocked() ) {
            $error = get_transient( 'loft1325_frontend_error' );
            delete_transient( 'loft1325_frontend_error' );
            echo '<div class="loft1325-lock-screen">';
            echo '<h2>' . esc_html__( 'Accès sécurisé', 'loft1325-booking-hub' ) . '</h2>';
            if ( $error ) {
                echo '<p class="loft1325-error">Mot de passe incorrect. Réessayez.</p>';
            }
            echo '<form method="post">';
            echo '<input type="password" name="loft1325_password" placeholder="Mot de passe" required />';
            echo '<button type="submit" name="loft1325_frontend_unlock" class="loft1325-primary">Déverrouiller</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            return ob_get_clean();
        }

        $counts = Loft1325_Bookings::get_dashboard_counts();
        $bookings = Loft1325_Bookings::get_bookings( 20 );

        echo '<header class="loft1325-header">';
        echo '<div>';
        echo '<span class="loft1325-eyebrow">Loft1325 Booking Hub</span>';
        echo '<h1>Hub de réservations</h1>';
        echo '</div>';
        echo '<a class="loft1325-primary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-new-booking' ) ) . '">+ Nouvelle réservation</a>';
        echo '</header>';

        echo '<div class="loft1325-grid">';
        echo '<div class="loft1325-card"><h3>Check-ins aujourd\'hui</h3><p class="loft1325-metric">' . esc_html( $counts['checkins'] ) . '</p></div>';
        echo '<div class="loft1325-card"><h3>Check-outs aujourd\'hui</h3><p class="loft1325-metric">' . esc_html( $counts['checkouts'] ) . '</p></div>';
        echo '<div class="loft1325-card"><h3>Occupés maintenant</h3><p class="loft1325-metric">' . esc_html( $counts['occupied'] ) . '</p></div>';
        echo '</div>';

        echo '<div class="loft1325-card">';
        echo '<div class="loft1325-card-header">';
        echo '<h3>Réservations à venir</h3>';
        echo '<a class="loft1325-secondary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-bookings' ) ) . '">Voir tout</a>';
        echo '</div>';
        echo '<div class="loft1325-grid">';
        foreach ( $bookings as $booking ) {
            $status = ucfirst( $booking['status'] );
            $key_status = $booking['butterfly_keychain_id'] ? 'Active' : 'Missing';
            echo '<div class="loft1325-card">';
            echo '<div class="loft1325-card-header">';
            echo '<div><h3>' . esc_html( $booking['loft_name'] ) . '</h3><span>' . esc_html( ucfirst( $booking['loft_type'] ) ) . '</span></div>';
            echo '<span class="loft1325-badge">' . esc_html( $status ) . '</span>';
            echo '</div>';
            echo '<p class="loft1325-guest">' . esc_html( $booking['guest_name'] ) . '</p>';
            echo '<p class="loft1325-dates">' . esc_html( loft1325_format_datetime_local( $booking['check_in_utc'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $booking['check_out_utc'] ) ) . '</p>';
            echo '<div class="loft1325-key">Clé: <span class="loft1325-badge">' . esc_html( $key_status ) . '</span></div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '</div>';

        return ob_get_clean();
    }
}
