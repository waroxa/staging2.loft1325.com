<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Admin_Pages {
    public static function boot() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_post_loft1325_save_settings', array( __CLASS__, 'save_settings' ) );
    }

    public static function register_menus() {
        add_menu_page(
            'Loft1325 Booking Hub',
            'Loft1325 Hub',
            'loft1325_manage_bookings',
            'loft1325-dashboard',
            array( __CLASS__, 'render_dashboard' ),
            'dashicons-building',
            3
        );

        add_submenu_page( 'loft1325-dashboard', 'Nouvelle réservation', 'Nouvelle réservation', 'loft1325_manage_bookings', 'loft1325-new-booking', array( __CLASS__, 'render_new_booking' ) );
        add_submenu_page( 'loft1325-dashboard', 'Aujourd\'hui', 'Aujourd\'hui', 'loft1325_manage_bookings', 'loft1325-dashboard', array( __CLASS__, 'render_dashboard' ) );
        add_submenu_page( 'loft1325-dashboard', 'Réservations', 'Réservations', 'loft1325_manage_bookings', 'loft1325-bookings', array( __CLASS__, 'render_bookings' ) );
        add_submenu_page( 'loft1325-dashboard', 'Disponibilités', 'Disponibilités', 'loft1325_manage_bookings', 'loft1325-availability', array( __CLASS__, 'render_availability' ) );
        add_submenu_page( 'loft1325-dashboard', 'Calendrier', 'Calendrier', 'loft1325_manage_bookings', 'loft1325-calendar', array( __CLASS__, 'render_calendar' ) );
        add_submenu_page( 'loft1325-dashboard', 'Lofts', 'Lofts', 'loft1325_manage_bookings', 'loft1325-lofts', array( __CLASS__, 'render_lofts' ) );
        add_submenu_page( 'loft1325-dashboard', 'Paramètres', 'Paramètres', 'loft1325_manage_bookings', 'loft1325-settings', array( __CLASS__, 'render_settings' ) );
        add_submenu_page( 'loft1325-dashboard', 'Journal', 'Journal', 'loft1325_manage_bookings', 'loft1325-log', array( __CLASS__, 'render_log' ) );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'loft1325' ) === false ) {
            return;
        }

        wp_enqueue_style( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.css', array(), LOFT1325_BOOKING_HUB_VERSION );
        wp_enqueue_script( 'loft1325-admin', LOFT1325_BOOKING_HUB_URL . 'assets/admin.js', array( 'jquery' ), LOFT1325_BOOKING_HUB_VERSION, true );
    }

    public static function save_settings() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_save_settings' );

        $settings = loft1325_get_settings();
        $settings['environment'] = isset( $_POST['environment'] ) ? sanitize_text_field( wp_unslash( $_POST['environment'] ) ) : 'production';
        if ( ! in_array( $settings['environment'], array( 'sandbox', 'production' ), true ) ) {
            $settings['environment'] = 'production';
        }

        $settings['client_id'] = sanitize_text_field( wp_unslash( $_POST['client_id'] ) );
        $settings['client_secret'] = sanitize_text_field( wp_unslash( $_POST['client_secret'] ) );
        $settings['building_id'] = sanitize_text_field( wp_unslash( $_POST['building_id'] ) );
        $settings['api_base_url'] = esc_url_raw( wp_unslash( $_POST['api_base_url'] ) );
        $settings['api_token'] = sanitize_text_field( wp_unslash( $_POST['api_token'] ) );
        $settings['default_access_point_ids'] = loft1325_sanitize_csv_ids( wp_unslash( $_POST['default_access_point_ids'] ) );
        $settings['default_device_ids'] = loft1325_sanitize_csv_ids( wp_unslash( $_POST['default_device_ids'] ) );
        $settings['building_timezone'] = sanitize_text_field( wp_unslash( $_POST['building_timezone'] ) );
        $settings['pass_naming_template'] = sanitize_text_field( wp_unslash( $_POST['pass_naming_template'] ) );
        $settings['staff_prefix'] = sanitize_text_field( wp_unslash( $_POST['staff_prefix'] ) );
        $settings['admin_alert_emails'] = sanitize_textarea_field( wp_unslash( $_POST['admin_alert_emails'] ?? '' ) );
        $settings['cleaning_team_emails'] = sanitize_textarea_field( wp_unslash( $_POST['cleaning_team_emails'] ?? '' ) );
        $settings['maintenance_team_emails'] = sanitize_textarea_field( wp_unslash( $_POST['maintenance_team_emails'] ?? '' ) );

        $admin_pass = sanitize_text_field( wp_unslash( $_POST['admin_hub_password'] ?? '' ) );
        $cleaning_pass = sanitize_text_field( wp_unslash( $_POST['cleaning_hub_password'] ?? '' ) );
        $maintenance_pass = sanitize_text_field( wp_unslash( $_POST['maintenance_hub_password'] ?? '' ) );

        if ( '' !== $admin_pass ) {
            $settings['admin_hub_password_hash'] = wp_hash_password( $admin_pass );
        }
        if ( '' !== $cleaning_pass ) {
            $settings['cleaning_hub_password_hash'] = wp_hash_password( $cleaning_pass );
        }
        if ( '' !== $maintenance_pass ) {
            $settings['maintenance_hub_password_hash'] = wp_hash_password( $maintenance_pass );
        }

        update_option( LOFT1325_SETTINGS_OPTION, $settings );

        // Keep shared ButterflyMX options in sync with wp-loft-booking-plugin.
        update_option( 'butterflymx_environment', $settings['environment'] );
        update_option( 'butterflymx_client_id', $settings['client_id'] );
        update_option( 'butterflymx_client_secret', $settings['client_secret'] );
        update_option( 'butterflymx_building_id', $settings['building_id'] );
        if ( ! empty( $settings['api_token'] ) ) {
            update_option( 'butterflymx_access_token_v4', $settings['api_token'] );
        }

        wp_safe_redirect( add_query_arg( 'loft1325_saved', '1', wp_get_referer() ) );
        exit;
    }

    private static function render_page_header( $title ) {
        echo '<div class="wrap loft1325-admin">';
        echo '<header class="loft1325-header">';
        echo '<div>';
        echo '<span class="loft1325-eyebrow">Loft1325 Booking Hub</span>';
        echo '<h1>' . esc_html( $title ) . '</h1>';
        echo '</div>';
        echo '<a class="loft1325-primary" href="' . esc_url( admin_url( 'admin.php?page=loft1325-new-booking' ) ) . '">+ Nouvelle réservation</a>';
        echo '</header>';
    }

    private static function render_locked_if_needed() {
        if ( Loft1325_Security::check_access() ) {
            return false;
        }

        self::render_page_header( 'Hub verrouillé' );
        Loft1325_Security::render_lock_screen();
        echo '</div>';
        return true;
    }

    public static function render_dashboard() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $counts = Loft1325_Bookings::get_dashboard_counts();
        $period = isset( $_GET['hub_period'] ) ? sanitize_key( wp_unslash( $_GET['hub_period'] ) ) : 'week';
        $bounds = Loft1325_Operations::get_period_bounds( $period );
        $period = $bounds['period'];
        $calendar_bookings = Loft1325_Bookings::get_bookings_for_range( $bounds['start'], $bounds['end'] );
        $period_titles = array(
            'today' => 'aujourd\'hui',
            'week' => '7 jours',
            'biweek' => '2 semaines',
            'month' => '1 mois',
            'year' => '1 an',
        );

        self::render_page_header( 'Aujourd\'hui' );
        echo '<div class="loft1325-grid">';
        echo '<div class="loft1325-card"><h3>Check-ins aujourd\'hui</h3><p class="loft1325-metric">' . esc_html( $counts['checkins'] ) . '</p></div>';
        echo '<div class="loft1325-card"><h3>Check-outs aujourd\'hui</h3><p class="loft1325-metric">' . esc_html( $counts['checkouts'] ) . '</p></div>';
        echo '<div class="loft1325-card"><h3>Occupés maintenant</h3><p class="loft1325-metric">' . esc_html( $counts['occupied'] ) . '</p></div>';
        echo '<div class="loft1325-card"><h3>Disponibles maintenant</h3>';
        if ( ! empty( $counts['available'] ) ) {
            echo '<ul class="loft1325-list">';
            foreach ( $counts['available'] as $row ) {
                echo '<li>' . esc_html( ucfirst( $row['loft_type'] ) ) . ' <strong>' . esc_html( $row['count'] ) . '</strong></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="loft1325-card loft1325-cta">';
        echo '<h3>Actions rapides</h3>';
        echo '<div class="loft1325-actions">';
        echo '<button class="loft1325-secondary">Créer clé</button>';
        echo '<button class="loft1325-secondary">Désactiver clé</button>';
        echo '<button class="loft1325-secondary">Prolonger séjour</button>';
        echo '</div>';
        echo '</div>';

        echo '<div class="loft1325-card loft1325-cta">';
        echo '<h3>Vue calendrier (Admin Hub)</h3>';
        echo '<p class="loft1325-meta">Vue ' . esc_html( $period_titles[ $period ] ?? $period ) . ' · ' . esc_html( loft1325_format_datetime_local( $bounds['start'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $bounds['end'] ) ) . '</p>';
        echo '<p class="loft1325-actions">';
        foreach ( array( 'year' => 'Année', 'month' => 'Mois', 'biweek' => '2 semaines', 'week' => '7 jours', 'today' => 'Aujourd\'hui' ) as $k => $label ) {
            $class = ( $period === $k ) ? 'loft1325-primary' : 'loft1325-secondary';
            echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( add_query_arg( array( 'page' => 'loft1325-dashboard', 'hub_period' => $k ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $label ) . '</a> ';
        }
        echo '</p>';
        echo '<div class="loft1325-timeline">';
        $has_calendar_rows = false;
        foreach ( $calendar_bookings as $calendar_booking ) {
            if ( ! in_array( $calendar_booking['status'], array( 'confirmed', 'checked_in', 'checked_out' ), true ) ) {
                continue;
            }

            $has_calendar_rows = true;
            echo '<div class="loft1325-timeline-row">';
            echo '<div class="loft1325-timeline-main"><strong>' . esc_html( $calendar_booking['loft_name'] ? $calendar_booking['loft_name'] : 'Loft' ) . '</strong><span class="loft1325-meta">' . esc_html( $calendar_booking['guest_name'] ) . '</span></div>';
            echo '<span class="loft1325-bar">' . esc_html( loft1325_format_datetime_local( $calendar_booking['check_in_utc'] ) . ' → ' . loft1325_format_datetime_local( $calendar_booking['check_out_utc'] ) ) . '</span>';
            echo '<span class="loft1325-meta">' . esc_html( ucfirst( str_replace( '_', ' ', $calendar_booking['status'] ) ) ) . '</span>';
            echo '</div>';
        }
        if ( ! $has_calendar_rows ) {
            echo '<div class="loft1325-callout">Aucune réservation confirmée pour cette période.</div>';
        }
        echo '</div>';
        echo '<p class="loft1325-actions">';
        echo '<a class="loft1325-secondary" href="' . esc_url( add_query_arg( array( 'page' => 'loft1325-calendar', 'view' => 'bookings', 'period' => $period ), admin_url( 'admin.php' ) ) ) . '">Ouvrir le calendrier complet</a>';
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_bookings() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $bookings = Loft1325_Bookings::get_bookings();

        self::render_page_header( 'Réservations' );

        if ( isset( $_GET['loft1325_sync_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-error"><p>La synchronisation ButterflyMX a échoué. Vérifiez la connexion API et réessayez.</p></div>';
        }

        if ( isset( $_GET['loft1325_synced'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $synced_count = isset( $_GET['loft1325_synced_count'] ) ? absint( wp_unslash( $_GET['loft1325_synced_count'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'Synchronisation ButterflyMX terminée. %d clés importées ou mises à jour.', 'loft1325-booking-hub' ), $synced_count ) . '</p></div>';
        }

        $sync_nonce = wp_create_nonce( 'loft1325_sync_keychains' );
        echo '<div class="loft1325-filter-bar">';
        echo '<span class="loft1325-chip is-active">Aujourd\'hui</span>';
        echo '<span class="loft1325-chip">7 jours</span>';
        echo '<span class="loft1325-chip">Mois</span>';
        echo '<span class="loft1325-chip">Tout</span>';
        echo '<input class="loft1325-search" type="search" placeholder="Nom, téléphone, email, ID" />';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-inline-form loft1325-sync-form">';
        echo '<input type="hidden" name="action" value="loft1325_sync_keychains" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $sync_nonce ) . '" />';
        echo '<button class="loft1325-primary loft1325-sync-submit" type="submit">';
        echo '<span class="loft1325-sync-submit-label">Sync ButterflyMX</span>';
        echo '<span class="loft1325-sync-submit-spinner" aria-hidden="true"></span>';
        echo '</button>';
        echo '<p class="loft1325-sync-status" aria-live="polite"></p>';
        echo '</form>';
        echo '</div>';

        echo '<div class="loft1325-grid">';
        foreach ( $bookings as $booking ) {
            $status = ucfirst( $booking['status'] );
            if ( 'tentative' === $booking['status'] ) {
                $status = 'Needs review';
            }
            $key_status = $booking['butterfly_keychain_id'] ? 'Active' : 'Missing';
            echo '<div class="loft1325-card">';
            echo '<div class="loft1325-card-header">';
            echo '<div><h3>' . esc_html( $booking['loft_name'] ) . '</h3><span>' . esc_html( ucfirst( $booking['loft_type'] ) ) . '</span></div>';
            echo '<span class="loft1325-badge">' . esc_html( $status ) . '</span>';
            echo '</div>';
            echo '<p class="loft1325-guest">' . esc_html( $booking['guest_name'] ) . '</p>';
            echo '<p class="loft1325-dates">' . esc_html( loft1325_format_datetime_local( $booking['check_in_utc'] ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $booking['check_out_utc'] ) ) . '</p>';
            echo '<div class="loft1325-key">Clé: <span class="loft1325-badge">' . esc_html( $key_status ) . '</span></div>';
            echo '<div class="loft1325-actions">';
            echo '<button class="loft1325-secondary">Edit</button>';
            echo '<button class="loft1325-primary">Créer/Renvoyer clé</button>';
            echo '<button class="loft1325-secondary">Révoquer</button>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public static function render_availability() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        self::render_page_header( 'Disponibilités' );
        echo '<div class="loft1325-card">';
        echo '<h3>Recherche rapide</h3>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
        echo '<label>Check-in</label><input type="date" name="check_in" required />';
        echo '<label>Check-out</label><input type="date" name="check_out" required />';
        echo '<label>Type</label>';
        echo '<select name="loft_type"><option value="simple">Simple</option><option value="double">Double</option><option value="penthouse">Penthouse</option></select>';
        echo '<button type="submit" class="loft1325-primary">Voir disponibilités</button>';
        echo '</form>';
        echo '<div class="loft1325-callout">Les disponibilités sont calculées en temps réel avec un contrôle d\'overlap sécurisé.</div>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_calendar() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'today';
        $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'bookings';
        $bookings = Loft1325_Operations::get_bookings_with_cleaning( $period );
        $tickets = Loft1325_Operations::get_maintenance_tickets();

        self::render_page_header( 'Booking Hub' );

        $bounds = Loft1325_Operations::get_period_bounds( $period );
        $period = $bounds['period'];
        $calendar_start = $bounds['start'];
        $calendar_end = $bounds['end'];
        $calendar_bookings = Loft1325_Bookings::get_bookings_for_range( $calendar_start, $calendar_end );
        $period_titles = array(
            'today' => 'aujourd\'hui',
            'week' => '7 jours',
            'biweek' => '2 semaines',
            'month' => '1 mois',
            'year' => '1 an',
        );

        echo '<div class="loft1325-card">';
        echo '<h3>Calendrier des réservations</h3>';
        echo '<p class="loft1325-meta">Vue ' . esc_html( $period_titles[ $period ] ?? $period ) . ' · ' . esc_html( loft1325_format_datetime_local( $calendar_start ) ) . ' → ' . esc_html( loft1325_format_datetime_local( $calendar_end ) ) . '</p>';
        echo '<p class="loft1325-meta">On garde tout clair et bien espacé pour vous aider à anticiper les réservations sur toute l\'année 💙</p>';
        echo '<p class="loft1325-actions">';
        foreach ( array( 'year' => 'Année', 'month' => 'Mois', 'biweek' => '2 semaines', 'week' => '7 jours', 'today' => 'Aujourd\'hui' ) as $k => $label ) {
            $class = ( $period === $k ) ? 'loft1325-primary' : 'loft1325-secondary';
            echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( add_query_arg( array( 'page' => 'loft1325-calendar', 'view' => $view, 'period' => $k ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $label ) . '</a> ';
        }
        echo '</p>';
        echo '<div class="loft1325-timeline">';
        $has_calendar_rows = false;
        foreach ( $calendar_bookings as $calendar_booking ) {
            if ( ! in_array( $calendar_booking['status'], array( 'confirmed', 'checked_in', 'checked_out' ), true ) ) {
                continue;
            }

            $label = $calendar_booking['loft_name'] ? $calendar_booking['loft_name'] : 'Loft';
            $dates = loft1325_format_datetime_local( $calendar_booking['check_in_utc'] ) . ' → ' . loft1325_format_datetime_local( $calendar_booking['check_out_utc'] );
            $status_label = ucfirst( str_replace( '_', ' ', $calendar_booking['status'] ) );
            $keychain = ! empty( $calendar_booking['butterfly_keychain_id'] ) ? sprintf( 'Clé #%d', absint( $calendar_booking['butterfly_keychain_id'] ) ) : 'Aucune clé';

            $has_calendar_rows = true;
            echo '<div class="loft1325-timeline-row">';
            echo '<div class="loft1325-timeline-main"><strong>' . esc_html( $label ) . '</strong><span class="loft1325-meta">' . esc_html( $calendar_booking['guest_name'] ) . '</span></div>';
            echo '<span class="loft1325-bar">' . esc_html( $dates ) . '</span>';
            echo '<span class="loft1325-meta">' . esc_html( $keychain ) . ' · ' . esc_html( $status_label ) . '</span>';
            echo '</div>';
        }
        if ( ! $has_calendar_rows ) {
            echo '<div class="loft1325-callout">Aucune réservation confirmée pour cette période.</div>';
        }

        echo '</div>';

        echo '<div class="loft1325-card">';
        echo '<h3>Vue calendrier + opérations</h3>';
        echo '<p class="loft1325-meta">Approuver/refuser les réservations, suivre le ménage et gérer la maintenance au même endroit.</p>';

        echo '<p class="loft1325-actions">';
        foreach ( array( 'bookings' => 'Réservations', 'cleaning' => 'Ménage', 'maintenance' => 'Maintenance' ) as $k => $label ) {
            $class = ( $view === $k ) ? 'loft1325-primary' : 'loft1325-secondary';
            echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( add_query_arg( array( 'page' => 'loft1325-calendar', 'view' => $k ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $label ) . '</a> ';
        }
        echo '</p>';

        if ( 'maintenance' === $view ) {
            echo '<h4>Nouveau ticket</h4>';
            echo '<form method="post" class="loft1325-form">';
            echo '<input type="hidden" name="loft1325_ops_action" value="maintenance_create" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
            echo '<label>Loft</label><input type="text" name="loft_label" required />';
            echo '<label>Titre</label><input type="text" name="title" required />';
            echo '<label>Détails</label><textarea name="details" rows="3" required></textarea>';
            echo '<label>Priorité</label><select name="priority"><option value="critical">Critical</option><option value="urgent">Urgent</option><option value="normal">Normal</option><option value="low">Low</option></select>';
            echo '<label>Email assigné</label><input type="email" name="assignee_email" />';
            echo '<label>Email client</label><input type="email" name="requested_by_email" />';
            echo '<button class="loft1325-primary">Créer ticket</button>';
            echo '</form>';

            echo '<div class="loft1325-grid">';
            foreach ( $tickets as $ticket ) {
                echo '<div class="loft1325-card">';
                echo '<h4>' . esc_html( $ticket['title'] ) . '</h4>';
                echo '<p>' . esc_html( $ticket['loft_label'] ) . ' · ' . esc_html( $ticket['priority'] ) . '</p>';
                echo '<p>' . esc_html( $ticket['details'] ) . '</p>';
                echo '<form method="post">';
                echo '<input type="hidden" name="loft1325_ops_action" value="maintenance_update" />';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_ops_action' ) ) . '" />';
                echo '<input type="hidden" name="ticket_id" value="' . esc_attr( $ticket['id'] ) . '" />';
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

    public static function render_lofts() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $lofts = Loft1325_Lofts::get_all();
        $seed_nonce = wp_create_nonce( 'loft1325_seed_lofts' );

        self::render_page_header( 'Lofts' );
        echo '<div class="loft1325-card loft1325-inline">';
        echo '<p>Chargez rapidement 22 lofts par défaut.</p>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="loft1325_seed_lofts" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $seed_nonce ) . '" />';
        echo '<button class="loft1325-secondary">Seed 22 lofts</button>';
        echo '</form>';
        echo '</div>';

        echo '<div class="loft1325-grid">';
        foreach ( $lofts as $loft ) {
            $nonce = wp_create_nonce( 'loft1325_save_loft' );
            echo '<div class="loft1325-card">';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
            echo '<input type="hidden" name="action" value="loft1325_save_loft" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '" />';
            echo '<input type="hidden" name="loft_id" value="' . esc_attr( $loft['id'] ) . '" />';
            echo '<label>Nom</label><input type="text" name="loft_name" value="' . esc_attr( $loft['loft_name'] ) . '" />';
            echo '<label>Type</label><select name="loft_type">';
            foreach ( array( 'simple', 'double', 'penthouse' ) as $type ) {
                $selected = selected( $loft['loft_type'], $type, false );
                echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . esc_html( ucfirst( $type ) ) . '</option>';
            }
            echo '</select>';
            echo '<label>Butterfly tenant_id</label><input type="text" name="butterfly_tenant_id" value="' . esc_attr( $loft['butterfly_tenant_id'] ) . '" />';
            echo '<label>Butterfly unit_id</label><input type="text" name="butterfly_unit_id" value="' . esc_attr( $loft['butterfly_unit_id'] ) . '" />';
            $checked = checked( $loft['is_active'], 1, false );
            echo '<label class="loft1325-toggle"><input type="checkbox" name="is_active" ' . $checked . ' /> Actif</label>';
            echo '<button class="loft1325-primary">Enregistrer</button>';
            echo '</form>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public static function render_new_booking() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $nonce = wp_create_nonce( 'loft1325_create_booking' );

        self::render_page_header( 'Nouvelle réservation' );
        echo '<div class="loft1325-card">';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
        echo '<input type="hidden" name="action" value="loft1325_create_booking" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<h3>1. Dates & type</h3>';
        echo '<label>Check-in</label><input type="datetime-local" name="check_in" required />';
        echo '<label>Check-out</label><input type="datetime-local" name="check_out" required />';
        echo '<label>Type de loft</label>';
        echo '<select name="loft_type" required><option value="simple">Simple</option><option value="double">Double</option><option value="penthouse">Penthouse</option></select>';
        echo '<label>Loft spécifique (optionnel)</label><input type="number" name="loft_id" placeholder="ID loft" />';

        echo '<h3>2. Invité</h3>';
        echo '<label>Nom</label><input type="text" name="guest_name" required />';
        echo '<label>Email</label><input type="email" name="guest_email" />';
        echo '<label>Téléphone</label><input type="text" name="guest_phone" />';

        echo '<h3>3. Notes</h3>';
        echo '<textarea name="notes" rows="3"></textarea>';

        echo '<label class="loft1325-toggle"><input type="checkbox" name="create_key" /> Créer clé ButterflyMX</label>';
        echo '<button class="loft1325-primary">FINALISER</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_settings() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $settings = loft1325_get_settings();
        $nonce = wp_create_nonce( 'loft1325_save_settings' );
        $password_nonce = wp_create_nonce( 'loft1325_update_password' );

        self::render_page_header( 'Paramètres' );
        echo '<div class="loft1325-card">';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
        echo '<input type="hidden" name="action" value="loft1325_save_settings" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<label>Environment</label><select name="environment"><option value="production" ' . selected( $settings['environment'], 'production', false ) . '>Production</option><option value="sandbox" ' . selected( $settings['environment'], 'sandbox', false ) . '>Sandbox</option></select>';
        echo '<label>Client ID</label><input type="text" name="client_id" value="' . esc_attr( $settings['client_id'] ) . '" />';
        echo '<label>Client Secret</label><input type="password" name="client_secret" value="' . esc_attr( $settings['client_secret'] ) . '" />';
        echo '<label>Building ID</label><input type="text" name="building_id" value="' . esc_attr( $settings['building_id'] ) . '" />';
        echo '<label>ButterflyMX API base URL</label><input type="text" name="api_base_url" value="' . esc_attr( $settings['api_base_url'] ) . '" />';
        echo '<label>API token / key</label><input type="password" name="api_token" value="' . esc_attr( $settings['api_token'] ) . '" />';
        echo '<label>Default access_point_ids (comma separated)</label><input type="text" name="default_access_point_ids" value="' . esc_attr( $settings['default_access_point_ids'] ) . '" />';
        echo '<label>Default device_ids (comma separated)</label><input type="text" name="default_device_ids" value="' . esc_attr( $settings['default_device_ids'] ) . '" />';
        echo '<label>Building timezone</label><input type="text" name="building_timezone" value="' . esc_attr( $settings['building_timezone'] ) . '" />';
        echo '<label>Pass naming template</label><input type="text" name="pass_naming_template" value="' . esc_attr( $settings['pass_naming_template'] ) . '" />';
        echo '<label>Staff naming prefix</label><input type="text" name="staff_prefix" value="' . esc_attr( $settings['staff_prefix'] ) . '" />';
        echo '<label>Emails admin alertes (séparés par virgule)</label><textarea name="admin_alert_emails" rows="2">' . esc_textarea( $settings['admin_alert_emails'] ) . '</textarea>';
        echo '<label>Emails équipe ménage</label><textarea name="cleaning_team_emails" rows="2">' . esc_textarea( $settings['cleaning_team_emails'] ) . '</textarea>';
        echo '<label>Emails équipe maintenance</label><textarea name="maintenance_team_emails" rows="2">' . esc_textarea( $settings['maintenance_team_emails'] ) . '</textarea>';
        echo '<label>Mot de passe page admin hub</label><input type="password" name="admin_hub_password" placeholder="Laisser vide pour conserver" />';
        echo '<label>Mot de passe page ménage</label><input type="password" name="cleaning_hub_password" placeholder="Laisser vide pour conserver" />';
        echo '<label>Mot de passe page maintenance</label><input type="password" name="maintenance_hub_password" placeholder="Laisser vide pour conserver" />';
        echo '<button class="loft1325-primary">Enregistrer</button>';
        echo '</form>';
        echo '</div>';

        echo '<div class="loft1325-card">';
        echo '<h3>Mot de passe d\'accès au hub</h3>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
        echo '<input type="hidden" name="action" value="loft1325_update_password" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $password_nonce ) . '" />';
        echo '<label>Nouveau mot de passe</label><input type="password" name="loft1325_new_password" required />';
        echo '<button class="loft1325-secondary">Mettre à jour</button>';
        echo '</form>';
        echo '</div>';

        echo '<div class="loft1325-card">';
        echo '<h3>Accès public (lien externe)</h3>';
        echo '<p>Créez une page WordPress et ajoutez le shortcode suivant :</p>';
        echo '<code>[loft1325_booking_hub]</code><br/><code>[loft1325_cleaning_hub]</code><br/><code>[loft1325_maintenance_hub]</code>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_log() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        global $wpdb;
        $log_table = $wpdb->prefix . 'loft1325_log';
        $entries = $wpdb->get_results( "SELECT * FROM {$log_table} ORDER BY created_at DESC LIMIT 50", ARRAY_A );

        self::render_page_header( 'Journal' );
        echo '<div class="loft1325-card">';
        echo '<div class="loft1325-log">';
        foreach ( $entries as $entry ) {
            echo '<div class="loft1325-log-entry">';
            echo '<strong>' . esc_html( $entry['action'] ) . '</strong>';
            echo '<span>' . esc_html( $entry['message'] ) . '</span>';
            echo '<span class="loft1325-meta">' . esc_html( $entry['created_at'] ) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
