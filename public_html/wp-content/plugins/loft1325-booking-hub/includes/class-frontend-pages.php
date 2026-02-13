<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Frontend_Pages {
    public static function boot() {
        add_shortcode( 'loft1325_booking_hub', array( __CLASS__, 'render_shortcode' ) );
        add_shortcode( 'loft1325_cleaning_hub', array( __CLASS__, 'render_cleaning_shortcode' ) );
        add_shortcode( 'loft1325_maintenance_hub', array( __CLASS__, 'render_maintenance_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'init', array( __CLASS__, 'handle_unlock' ) );
    }

    public static function enqueue_assets() {
        if ( ! self::is_shortcode_present() ) {
            return;
        }

        wp_enqueue_style( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.css', array(), LOFT1325_BOOKING_HUB_VERSION );
        wp_enqueue_script( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.js', array( 'jquery' ), LOFT1325_BOOKING_HUB_VERSION, true );
        wp_enqueue_style(
            'loft1325-frontend-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap',
            array(),
            null
        );
    }

    private static function is_shortcode_present() {
        if ( ! is_singular() ) {
            return false;
        }

        global $post;
        if ( ! $post ) {
            return false;
        }

        return has_shortcode( $post->post_content, 'loft1325_booking_hub' ) || has_shortcode( $post->post_content, 'loft1325_cleaning_hub' ) || has_shortcode( $post->post_content, 'loft1325_maintenance_hub' );
    }

    public static function handle_unlock() {
        if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['loft1325_frontend_unlock'] ) ) {
            return;
        }

        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'loft1325_frontend_unlock' ) ) {
            return;
        }

        $scope = isset( $_POST['loft1325_scope'] ) ? sanitize_key( wp_unslash( $_POST['loft1325_scope'] ) ) : 'admin';
        $hash_key = 'password_hash';
        if ( 'cleaning' === $scope ) {
            $hash_key = 'cleaning_hub_password_hash';
        } elseif ( 'maintenance' === $scope ) {
            $hash_key = 'maintenance_hub_password_hash';
        } elseif ( 'admin' === $scope ) {
            $hash_key = 'admin_hub_password_hash';
        }

        $posted_redirect = isset( $_POST['loft1325_redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['loft1325_redirect_to'] ) ) : '';
        $redirect_url = $posted_redirect ? wp_validate_redirect( $posted_redirect, home_url( '/' ) ) : ( wp_get_referer() ?: self::current_request_url() );
        $password = isset( $_POST['loft1325_password'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_password'] ) ) : '';
        $settings = loft1325_get_settings();
        $stored_hash = isset( $settings[ $hash_key ] ) ? (string) $settings[ $hash_key ] : '';

        if ( ! $stored_hash || ! wp_check_password( $password, $stored_hash ) ) {
            set_transient( 'loft1325_frontend_error_' . $scope, true, MINUTE_IN_SECONDS );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        $expires = time() + HOUR_IN_SECONDS * 12;
        $cookie_name = 'loft1325_hub_unlocked_' . $scope;
        setcookie( $cookie_name, '1', $expires, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        $_COOKIE[ $cookie_name ] = '1';

        wp_safe_redirect( $redirect_url );
        exit;
    }

    private static function is_unlocked( $scope = 'admin' ) {
        $cookie_name = 'loft1325_hub_unlocked_' . $scope;
        return isset( $_COOKIE[ $cookie_name ] ) && '1' === $_COOKIE[ $cookie_name ];
    }

    private static function current_request_url() {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        return home_url( $request_uri );
    }

    private static function render_stays_calendar( $period = 'month' ) {
        $bookings = Loft1325_Operations::get_bookings_with_cleaning( $period );

        echo '<div class="loft1325-card loft1325-calendar">';
        echo '<div class="loft1325-card-header">';
        echo '<h3>Calendrier des séjours</h3>';
        echo '<span class="loft1325-meta">Vue ' . esc_html( ucfirst( $period ) ) . '</span>';
        echo '</div>';

        if ( empty( $bookings ) ) {
            echo '<p class="loft1325-meta">Aucun séjour trouvé pour cette période.</p>';
            echo '</div>';
            return;
        }

        echo '<div class="loft1325-timeline">';
        foreach ( $bookings as $booking ) {
            $check_in = loft1325_format_datetime_local( $booking['check_in_utc'] );
            $check_out = loft1325_format_datetime_local( $booking['check_out_utc'] );
            echo '<div class="loft1325-timeline-row">';
            echo '<div><strong>' . esc_html( $booking['loft_name'] ?: 'Loft' ) . '</strong><br /><span class="loft1325-meta">' . esc_html( $booking['guest_name'] ) . '</span></div>';
            echo '<div class="loft1325-bar">' . esc_html( $check_in . ' → ' . $check_out ) . '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public static function render_shortcode() {
        ob_start();

        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo_url = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '';

        if ( ! $logo_url ) {
            $fallback_logo_path = '/wp-content/uploads/2024/06/Asset-1.png';
            $fallback_logo_file = ABSPATH . ltrim( $fallback_logo_path, '/' );

            if ( file_exists( $fallback_logo_file ) ) {
                $logo_url = home_url( $fallback_logo_path );
            }
        }
        $site_name = get_bloginfo( 'name' );
        $menu = wp_nav_menu(
            array(
                'theme_location' => 'main-menu',
                'container' => false,
                'menu_class' => 'loft1325-mobile-nav-list',
                'fallback_cb' => false,
                'echo' => false,
            )
        );

        echo '<div class="loft1325-admin loft1325-frontend">';
        echo '<div class="loft1325-frontend-shell">';
        echo '<header class="loft1325-mobile-header">';
        echo '<details class="loft1325-mobile-nav">';
        echo '<summary class="loft1325-mobile-menu" aria-label="Menu">';
        echo '<span class="loft1325-mobile-menu-bar"></span>';
        echo '<span class="loft1325-mobile-menu-bar"></span>';
        echo '<span class="loft1325-mobile-menu-bar"></span>';
        echo '</summary>';
        if ( $menu ) {
            echo '<nav class="loft1325-mobile-nav-panel" aria-label="Navigation">';
            echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</nav>';
        }
        echo '</details>';
        echo '<a class="loft1325-mobile-logo" href="' . esc_url( home_url( '/' ) ) . '">';
        if ( $logo_url ) {
            echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $site_name ) . '" />';
        } else {
            echo '<span class="loft1325-mobile-logo-text">' . esc_html( $site_name ) . '</span>';
        }
        echo '</a>';
        echo '<div class="loft1325-mobile-lang">FR · EN</div>';
        echo '</header>';

        if ( ! self::is_unlocked( 'admin' ) ) {
            $error = get_transient( 'loft1325_frontend_error_admin' );
            delete_transient( 'loft1325_frontend_error_admin' );
            $unlock_action = self::current_request_url();
            echo '<div class="loft1325-lock-screen">';
            echo '<h2>' . esc_html__( 'Accès sécurisé', 'loft1325-booking-hub' ) . '</h2>';
            if ( $error ) {
                echo '<p class="loft1325-error">Mot de passe incorrect. Réessayez.</p>';
            }
            echo '<form method="post" action="' . esc_url( $unlock_action ) . '">';
            echo '<input type="hidden" name="loft1325_frontend_unlock" value="1" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_frontend_unlock' ) ) . '" />';
            echo '<input type="hidden" name="loft1325_scope" value="admin" />';
            echo '<input type="hidden" name="loft1325_redirect_to" value="' . esc_attr( $unlock_action ) . '" />';
            echo '<input type="password" name="loft1325_password" placeholder="Mot de passe" required />';
            echo '<button type="submit" class="loft1325-primary">Déverrouiller</button>';
            echo '</form>';
            echo '</div>';
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

        self::render_stays_calendar( 'month' );

        echo '</div>';

        echo '</div>';

        return ob_get_clean();
    }


    public static function render_cleaning_shortcode() {
        ob_start();
        $unlock_action = self::current_request_url();
        if ( ! self::is_unlocked( 'cleaning' ) ) {
            $error = get_transient( 'loft1325_frontend_error_cleaning' );
            delete_transient( 'loft1325_frontend_error_cleaning' );
            echo '<div class="loft1325-lock-screen"><h2>Accès ménage</h2>';
            if ( $error ) {
                echo '<p class="loft1325-error">Mot de passe incorrect.</p>';
            }
            echo '<form method="post" action="' . esc_url( $unlock_action ) . '"><input type="hidden" name="loft1325_frontend_unlock" value="1" /><input type="hidden" name="loft1325_scope" value="cleaning" /><input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_frontend_unlock' ) ) . '" /><input type="hidden" name="loft1325_redirect_to" value="' . esc_attr( $unlock_action ) . '" /><input type="password" name="loft1325_password" required /><button class="loft1325-primary">Déverrouiller</button></form></div>';
            return ob_get_clean();
        }

        $bookings = Loft1325_Operations::get_bookings_with_cleaning( 'today' );
        echo '<div class="loft1325-admin"><h2 class="loft1325-hub-title">Cleaning Hub</h2><div class="loft1325-grid">';
        foreach ( $bookings as $booking ) {
            echo '<div class="loft1325-card"><h3>' . esc_html( $booking['loft_name'] ) . '</h3><p>' . esc_html( $booking['guest_name'] ) . '</p><p>Status: ' . esc_html( $booking['cleaning_status'] ) . '</p>';
            echo '<form method="post" class="loft1325-actions"><input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" /><input type="hidden" name="booking_id" value="' . esc_attr( $booking['id'] ) . '" />';
            echo '<button name="loft1325_ops_action" value="dirty" class="loft1325-secondary">Dirty</button><button name="loft1325_ops_action" value="in_progress" class="loft1325-secondary">In progress</button><button name="loft1325_ops_action" value="cleaned" class="loft1325-primary">Cleaned</button></form></div>';
        }
        echo '</div></div>';
        return ob_get_clean();
    }

    public static function render_maintenance_shortcode() {
        ob_start();
        $unlock_action = self::current_request_url();
        if ( ! self::is_unlocked( 'maintenance' ) ) {
            $error = get_transient( 'loft1325_frontend_error_maintenance' );
            delete_transient( 'loft1325_frontend_error_maintenance' );
            echo '<div class="loft1325-lock-screen"><h2>Accès maintenance</h2>';
            if ( $error ) {
                echo '<p class="loft1325-error">Mot de passe incorrect.</p>';
            }
            echo '<form method="post" action="' . esc_url( $unlock_action ) . '"><input type="hidden" name="loft1325_frontend_unlock" value="1" /><input type="hidden" name="loft1325_scope" value="maintenance" /><input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_frontend_unlock' ) ) . '" /><input type="hidden" name="loft1325_redirect_to" value="' . esc_attr( $unlock_action ) . '" /><input type="password" name="loft1325_password" required /><button class="loft1325-primary">Déverrouiller</button></form></div>';
            return ob_get_clean();
        }

        $tickets = Loft1325_Operations::get_maintenance_tickets();
        echo '<div class="loft1325-admin"><h2>Maintenance Hub</h2><ul>';
        if ( empty( $tickets ) ) {
            echo '<li>Aucun ticket de maintenance pour le moment.</li>';
        } else {
            foreach ( $tickets as $ticket ) {
                echo '<li><strong>' . esc_html( $ticket['title'] ) . '</strong> — ' . esc_html( $ticket['status'] ) . ' (' . esc_html( $ticket['priority'] ) . ')</li>';
            }
        }
        echo '</ul>';
        self::render_stays_calendar( 'week' );
        echo '</div>';
        return ob_get_clean();
    }

}
