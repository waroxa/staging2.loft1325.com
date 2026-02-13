<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Frontend_Pages {
    private const FRONTEND_SESSION_COOKIE = 'loft1325_hub_frontend_session';

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
        if ( ! in_array( $scope, array( 'admin', 'cleaning', 'maintenance' ), true ) ) {
            $scope = 'admin';
        }

        $hash_key = 'admin_hub_password_hash';
        if ( 'cleaning' === $scope ) {
            $hash_key = 'cleaning_hub_password_hash';
        } elseif ( 'maintenance' === $scope ) {
            $hash_key = 'maintenance_hub_password_hash';
        }

        $redirect_url = wp_get_referer() ?: home_url( '/' );
        $password = isset( $_POST['loft1325_password'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_password'] ) ) : '';
        $settings = loft1325_get_settings();
        $stored_hash = isset( $settings[ $hash_key ] ) ? (string) $settings[ $hash_key ] : '';

        if ( ! $stored_hash || ! wp_check_password( $password, $stored_hash ) ) {
            set_transient( 'loft1325_frontend_error_' . $scope, true, MINUTE_IN_SECONDS );
            wp_safe_redirect( $redirect_url );
            exit;
        }

        $expires = time() + HOUR_IN_SECONDS * 12;
        self::set_frontend_session_cookie( $scope, $expires );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    private static function set_frontend_session_cookie( $scope, $expires ) {
        $payload = implode( '|', array( $scope, (string) $expires, self::sign_unlock_token( $scope, $expires ) ) );

        $cookie_args = array(
            'expires'  => $expires,
            'path'     => COOKIEPATH ? COOKIEPATH : '/',
            'domain'   => COOKIE_DOMAIN,
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        );

        setcookie( self::FRONTEND_SESSION_COOKIE, $payload, $cookie_args );
        $_COOKIE[ self::FRONTEND_SESSION_COOKIE ] = $payload;
    }

    private static function sign_unlock_token( $scope, $expires ) {
        $message = sanitize_key( $scope ) . '|' . absint( $expires ) . '|' . self::get_user_agent_hash();
        return hash_hmac( 'sha256', $message, wp_salt( 'auth' ) );
    }

    private static function get_user_agent_hash() {
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        return hash( 'sha256', $user_agent );
    }

    public static function get_frontend_unlock_scope() {
        if ( empty( $_COOKIE[ self::FRONTEND_SESSION_COOKIE ] ) ) {
            return '';
        }

        $parts = explode( '|', (string) $_COOKIE[ self::FRONTEND_SESSION_COOKIE ] );
        if ( 3 !== count( $parts ) ) {
            return '';
        }

        list( $scope, $expires, $signature ) = $parts;
        $scope = sanitize_key( $scope );
        $expires = absint( $expires );

        if ( ! in_array( $scope, array( 'admin', 'cleaning', 'maintenance' ), true ) ) {
            return '';
        }

        if ( $expires < time() ) {
            return '';
        }

        $expected_signature = self::sign_unlock_token( $scope, $expires );
        if ( ! hash_equals( $expected_signature, (string) $signature ) ) {
            return '';
        }

        return $scope;
    }

    private static function is_unlocked() {
        return '' !== self::get_frontend_unlock_scope();
    }

    private static function get_frontend_hub_url( $args = array() ) {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $url = home_url( $request_uri );

        if ( empty( $args ) ) {
            return $url;
        }

        return add_query_arg( $args, $url );
    }

    private static function render_lock_screen( $scope ) {
        $error = get_transient( 'loft1325_frontend_error_' . $scope );
        delete_transient( 'loft1325_frontend_error_' . $scope );

        echo '<div class="loft1325-lock-screen"><h2>Accès hub booking</h2>';
        echo '<p>Entrez votre mot de passe pour accéder à toutes les opérations: réservations, ménage et maintenance.</p>';
        if ( $error ) {
            echo '<p class="loft1325-error">Mot de passe incorrect.</p>';
        }

        echo '<form method="post">';
        echo '<input type="hidden" name="loft1325_frontend_unlock" value="1" />';
        echo '<input type="hidden" name="loft1325_scope" value="' . esc_attr( $scope ) . '" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_frontend_unlock' ) ) . '" />';
        echo '<input type="password" name="loft1325_password" required />';
        echo '<button class="loft1325-primary">Déverrouiller</button>';
        echo '</form></div>';
    }

    private static function render_frontend_hub( $scope = 'admin' ) {
        if ( ! self::is_unlocked() ) {
            self::render_lock_screen( $scope );
            return;
        }

        $period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'today';
        $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'bookings';
        $bookings = Loft1325_Operations::get_bookings_with_cleaning( $period );
        $tickets = Loft1325_Operations::get_maintenance_tickets();
        $bounds = Loft1325_Operations::get_period_bounds( $period );
        $period = $bounds['period'];

        echo '<div class="loft1325-admin">';
        echo '<header class="loft1325-header">';
        echo '<div><span class="loft1325-eyebrow">Loft1325 Booking Hub</span><h1>Booking Hub</h1></div>';
        echo '<a class="loft1325-primary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-new-booking' ) ) . '">+ Nouvelle réservation</a>';
        echo '</header>';

        echo '<div class="loft1325-card">';
        echo '<h3>Vue calendrier + opérations</h3>';
        echo '<p class="loft1325-meta">Approuver/refuser les réservations, suivre le ménage et gérer la maintenance au même endroit.</p>';
        echo '<p class="loft1325-meta">Période: ' . esc_html( loft1325_format_datetime_local( $bounds['start'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $bounds['end'] ) ) . '</p>';

        echo '<p class="loft1325-actions">';
        foreach ( array( 'today' => 'Aujourd\'hui', 'week' => '7 jours', 'biweek' => '2 semaines', 'month' => '1 mois', 'year' => '1 an' ) as $period_key => $period_label ) {
            $period_class = ( $period_key === $period ) ? 'loft1325-primary' : 'loft1325-secondary';
            echo '<a class="' . esc_attr( $period_class ) . '" href="' . esc_url( self::get_frontend_hub_url( array( 'period' => $period_key, 'view' => $view ) ) ) . '">' . esc_html( $period_label ) . '</a> ';
        }
        echo '</p>';

        echo '<p class="loft1325-actions">';
        foreach ( array( 'bookings' => 'Réservations', 'cleaning' => 'Ménage', 'maintenance' => 'Maintenance' ) as $view_key => $label ) {
            $view_class = ( $view === $view_key ) ? 'loft1325-primary' : 'loft1325-secondary';
            echo '<a class="' . esc_attr( $view_class ) . '" href="' . esc_url( self::get_frontend_hub_url( array( 'view' => $view_key, 'period' => $period ) ) ) . '">' . esc_html( $label ) . '</a> ';
        }
        echo '</p>';

        if ( 'maintenance' === $view ) {
            echo '<div class="loft1325-card">';
            echo '<h3>Nouveau ticket maintenance</h3>';
            echo '<form method="post" class="loft1325-form">';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
            echo '<input type="hidden" name="loft1325_ops_action" value="maintenance_create" />';
            echo '<label>Loft</label><input type="text" name="loft_label" required />';
            echo '<label>Titre</label><input type="text" name="title" required />';
            echo '<label>Détails</label><textarea name="details" rows="3" required></textarea>';
            echo '<label>Priorité</label><select name="priority"><option value="low">Low</option><option value="normal">Normal</option><option value="high">High</option><option value="critical">Critical</option></select>';
            echo '<label>Email assigné</label><input type="email" name="assignee_email" />';
            echo '<label>Email demandeur</label><input type="email" name="requested_by_email" />';
            echo '<button class="loft1325-primary">Créer ticket</button>';
            echo '</form></div>';

            echo '<div class="loft1325-grid">';
            foreach ( $tickets as $ticket ) {
                echo '<div class="loft1325-card"><h3>' . esc_html( $ticket['title'] ) . '</h3><p class="loft1325-meta">' . esc_html( $ticket['loft_label'] ) . ' · ' . esc_html( $ticket['priority'] ) . '</p>';
                echo '<p>' . esc_html( $ticket['details'] ) . '</p>';
                echo '<form method="post" class="loft1325-actions">';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
                echo '<input type="hidden" name="ticket_id" value="' . esc_attr( $ticket['id'] ) . '" />';
                echo '<input type="hidden" name="loft1325_ops_action" value="maintenance_update" />';
                echo '<select name="status"><option value="todo">Todo</option><option value="in_progress">In progress</option><option value="done">Done</option></select>';
                echo '<button class="loft1325-secondary">Mettre à jour</button>';
                echo '</form></div>';
            }
            echo '</div>';
        } else {
            echo '<div class="loft1325-grid">';
            foreach ( $bookings as $booking ) {
                echo '<div class="loft1325-card">';
                echo '<div class="loft1325-card-header"><div><h3>' . esc_html( $booking['loft_name'] ) . '</h3><span>' . esc_html( $booking['guest_name'] ) . '</span></div><span class="loft1325-badge">' . esc_html( ucfirst( $booking['status'] ) ) . '</span></div>';
                echo '<p class="loft1325-dates">' . esc_html( loft1325_format_datetime_local( $booking['check_in_utc'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $booking['check_out_utc'] ) ) . '</p>';
                echo '<p>Cleaning: <strong>' . esc_html( $booking['cleaning_status'] ) . '</strong></p>';
                echo '<form method="post" class="loft1325-actions">';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
                echo '<input type="hidden" name="booking_id" value="' . esc_attr( $booking['id'] ) . '" />';
                if ( 'bookings' === $view ) {
                    echo '<button class="loft1325-primary" name="loft1325_ops_action" value="approve">Approve</button>';
                    echo '<button class="loft1325-secondary" name="loft1325_ops_action" value="reject">Reject</button>';
                } else {
                    echo '<button class="loft1325-secondary" name="loft1325_ops_action" value="dirty">Dirty</button>';
                    echo '<button class="loft1325-secondary" name="loft1325_ops_action" value="in_progress">In progress</button>';
                    echo '<button class="loft1325-primary" name="loft1325_ops_action" value="cleaned">Cleaned</button>';
                    echo '<button class="loft1325-secondary" name="loft1325_ops_action" value="issue">Needs maintenance</button>';
                }
                echo '</form></div>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    public static function render_shortcode() {
        ob_start();
        self::render_frontend_hub( 'admin' );
        return ob_get_clean();
    }

    public static function render_cleaning_shortcode() {
        ob_start();
        self::render_frontend_hub( 'cleaning' );
        return ob_get_clean();
    }

    public static function render_maintenance_shortcode() {
        ob_start();
        self::render_frontend_hub( 'maintenance' );
        return ob_get_clean();
    }
}
