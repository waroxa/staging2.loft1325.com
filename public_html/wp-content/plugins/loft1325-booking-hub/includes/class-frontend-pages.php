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

    private static function get_frontend_hub_url( $args = array() ) {
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $url = home_url( $request_uri );

        if ( empty( $args ) ) {
            return $url;
        }

        return add_query_arg( $args, $url );
    }

    private static function render_frontend_hub() {
        if ( ! is_user_logged_in() || ! current_user_can( 'loft1325_manage_bookings' ) ) {
            $login_url = wp_login_url( self::get_frontend_hub_url() );
            echo '<div class="loft1325-admin">';
            echo '<div class="loft1325-card">';
            echo '<h3>' . esc_html__( 'Connexion requise', 'loft1325-booking-hub' ) . '</h3>';
            echo '<p>' . esc_html__( 'Vous devez utiliser un utilisateur WordPress autorisé pour accéder au Booking Hub.', 'loft1325-booking-hub' ) . '</p>';
            echo '<p><a class="loft1325-primary" href="' . esc_url( $login_url ) . '">' . esc_html__( 'Se connecter', 'loft1325-booking-hub' ) . '</a></p>';
            echo '</div>';
            echo '</div>';
            return;
        }

        $period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'today';
        $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'bookings';
        $current_view_url = self::get_frontend_hub_url(
            array(
                'view' => $view,
                'period' => $period,
            )
        );
        $bookings = Loft1325_Operations::get_bookings_with_cleaning( $period );
        $tickets = Loft1325_Operations::get_maintenance_tickets();
        $bounds = Loft1325_Operations::get_period_bounds( $period );
        $period = $bounds['period'];

        echo '<div class="loft1325-admin">';
        echo '<header class="loft1325-header">';
        echo '<div><span class="loft1325-eyebrow">Loft1325 Booking Hub</span><h1>Booking Hub</h1></div>';
        echo '<a class="loft1325-primary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-new-booking' ) ) . '">+ Nouvelle réservation</a>';
        echo '</header>';

        if ( ! empty( $_GET['loft1325_ops_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-error"><p>Une erreur est survenue pendant l&rsquo;action demandée.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_conflict'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-warning"><p>Impossible de confirmer: ce loft est déjà occupé sur cette période.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_not_free'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-warning"><p>Le loft n&rsquo;est plus FREE sur cette période. Rafraîchissez la vue avant de continuer.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_free_confirmed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $confirmed_loft_id = isset( $_GET['loft1325_loft_id'] ) ? absint( $_GET['loft1325_loft_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $confirmed_label = $confirmed_loft_id ? sprintf( ' (ID #%d)', $confirmed_loft_id ) : '';
            echo '<div class="notice notice-success"><p>Disponibilité FREE confirmée' . esc_html( $confirmed_label ) . '.</p></div>';
        }

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
            echo '<input type="hidden" name="loft1325_redirect" value="' . esc_url( $current_view_url ) . '" />';
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
                echo '<input type="hidden" name="loft1325_redirect" value="' . esc_url( $current_view_url ) . '" />';
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
                $status_class = 'loft1325-badge--' . sanitize_html_class( (string) $booking['status'] );
                $key_missing = empty( $booking['butterfly_keychain_id'] );
                echo '<div class="loft1325-card">';
                echo '<div class="loft1325-card-header"><div><h3>' . esc_html( $booking['loft_name'] ) . '</h3><span>' . esc_html( $booking['guest_name'] ) . '</span></div><span class="loft1325-badge ' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( $booking['status'] ) ) . '</span></div>';
                echo '<p class="loft1325-dates">' . esc_html( loft1325_format_datetime_local( $booking['check_in_utc'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $booking['check_out_utc'] ) ) . '</p>';
                echo '<p>Cleaning: <strong>' . esc_html( $booking['cleaning_status'] ) . '</strong></p>';
                if ( $key_missing ) {
                    echo '<p><span class="loft1325-badge loft1325-badge--free">FREE · no key yet</span></p>';
                }
                echo '<form method="post" class="loft1325-actions">';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
                echo '<input type="hidden" name="loft1325_redirect" value="' . esc_url( $current_view_url ) . '" />';
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

            if ( 'bookings' === $view ) {
                $availability_rows = Loft1325_Operations::get_loft_availability( $period );
                echo '<div class="loft1325-card">';
                echo '<h4>Disponibilité des lofts</h4>';
                echo '<p class="loft1325-meta">FREE = aucun séjour confirmé/checked-in sur la période sélectionnée.</p>';
                echo '<div class="loft1325-grid">';
                foreach ( $availability_rows as $row ) {
                    $is_busy = ! empty( $row['is_busy'] );
                    $badge_class = $is_busy ? 'loft1325-badge--busy' : 'loft1325-badge--free';
                    $badge_label = $is_busy ? 'BUSY' : 'FREE';
                    echo '<div class="loft1325-card">';
                    echo '<div class="loft1325-card-header"><strong>' . esc_html( $row['loft_name'] ) . '</strong><span class="loft1325-badge ' . esc_attr( $badge_class ) . '">' . esc_html( $badge_label ) . '</span></div>';
                    echo '<p class="loft1325-meta">Type: ' . esc_html( ucfirst( $row['loft_type'] ) ) . '</p>';
                    if ( ! $is_busy ) {
                        echo '<form method="post" class="loft1325-actions">';
                        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
                        echo '<input type="hidden" name="loft1325_redirect" value="' . esc_url( $current_view_url ) . '" />';
                        echo '<input type="hidden" name="loft1325_ops_action" value="confirm_free" />';
                        echo '<input type="hidden" name="loft_id" value="' . esc_attr( $row['id'] ) . '" />';
                        echo '<input type="hidden" name="period" value="' . esc_attr( $period ) . '" />';
                        echo '<button class="loft1325-secondary" type="submit">Confirmer FREE</button>';
                        echo '</form>';
                        echo '<p class="loft1325-actions"><a class="loft1325-primary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-new-booking&loft_id=' . absint( $row['id'] ) ) ) . '">Créer une réservation</a></p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        }

        self::render_stays_calendar( 'month' );

        echo '</div>';
        echo '</div>';
    }

    private static function render_stays_calendar( $period = 'month' ) {
        $bounds = Loft1325_Operations::get_period_bounds( $period );
        $bookings = Loft1325_Operations::get_bookings_with_cleaning( $bounds['period'] );

        echo '<div class="loft1325-card">';
        echo '<h3>Séjours confirmés</h3>';
        echo '<p class="loft1325-meta">' . esc_html( loft1325_format_datetime_local( $bounds['start'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $bounds['end'] ) ) . '</p>';

        $has_rows = false;
        echo '<div class="loft1325-timeline">';
        foreach ( $bookings as $booking ) {
            if ( ! in_array( $booking['status'], array( 'confirmed', 'checked_in', 'checked_out' ), true ) ) {
                continue;
            }

            $has_rows = true;
            echo '<div class="loft1325-timeline-row">';
            echo '<div class="loft1325-timeline-main"><strong>' . esc_html( $booking['loft_name'] ? $booking['loft_name'] : 'Loft' ) . '</strong><span class="loft1325-meta">' . esc_html( $booking['guest_name'] ) . '</span></div>';
            echo '<span class="loft1325-bar">' . esc_html( loft1325_format_datetime_local( $booking['check_in_utc'] ) . ' → ' . loft1325_format_datetime_local( $booking['check_out_utc'] ) ) . '</span>';
            echo '<span class="loft1325-meta">' . esc_html( ucfirst( str_replace( '_', ' ', $booking['status'] ) ) ) . '</span>';
            echo '</div>';
        }

        if ( ! $has_rows ) {
            echo '<p>Aucun séjour confirmé pour cette période.</p>';
        }

        echo '</div>';
        echo '</div>';
    }

    public static function render_shortcode() {
        ob_start();
        self::render_frontend_hub();
        return ob_get_clean();
    }

    public static function render_cleaning_shortcode() {
        ob_start();
        self::render_frontend_hub();
        return ob_get_clean();
    }

    public static function render_maintenance_shortcode() {
        ob_start();
        self::render_frontend_hub();
        return ob_get_clean();
    }
}
