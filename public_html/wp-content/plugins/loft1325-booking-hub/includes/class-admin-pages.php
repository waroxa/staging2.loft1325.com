<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Admin_Pages {
    public static function boot() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_post_loft1325_save_settings', array( __CLASS__, 'save_settings' ) );
        add_action( 'admin_post_loft1325_view_availability', array( __CLASS__, 'handle_view_availability' ) );
        add_action( 'admin_post_loft1325_run_discovery_audit', array( __CLASS__, 'handle_run_discovery_audit' ) );
        add_action( 'admin_post_loft1325_run_loft_categorization', array( __CLASS__, 'handle_run_loft_categorization' ) );
        add_action( 'wp_ajax_loft1325_test_butterfly_connection', array( __CLASS__, 'ajax_test_butterfly_connection' ) );
    }

    public static function handle_view_availability() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_view_availability' );

        $check_in = isset( $_POST['check_in'] ) ? sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) : '';
        $check_out = isset( $_POST['check_out'] ) ? sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) : '';
        $loft_type = isset( $_POST['loft_type'] ) ? sanitize_key( wp_unslash( $_POST['loft_type'] ) ) : '';

        if ( ! in_array( $loft_type, array( 'simple', 'double', 'penthouse' ), true ) ) {
            $loft_type = 'simple';
        }

        $redirect_args = array(
            'page' => 'loft1325-availability',
            'check_in' => $check_in,
            'check_out' => $check_out,
            'loft_type' => $loft_type,
        );

        wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
        exit;
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
        if ( current_user_can( 'loft1325_manage_bookings' ) ) {
            return false;
        }

        wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
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

        if ( isset( $_GET['loft1325_key_created'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success"><p>Clé ButterflyMX créée ou renvoyée.</p></div>';
        }

        if ( isset( $_GET['loft1325_revoked'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success"><p>Clé ButterflyMX révoquée.</p></div>';
        }

        if ( isset( $_GET['loft1325_key_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-error"><p>Impossible de traiter la clé ButterflyMX pour cette réservation.</p></div>';
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
            $legacy_edit_url = add_query_arg(
                array(
                    'page'       => 'wp_loft_booking_bookings',
                    'booking_id' => absint( $booking['id'] ),
                ),
                admin_url( 'admin.php' )
            );
            echo '<a class="loft1325-secondary" href="' . esc_url( $legacy_edit_url ) . '">Edit</a>';

            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-inline-form">';
            echo '<input type="hidden" name="action" value="loft1325_create_key" />';
            echo '<input type="hidden" name="booking_id" value="' . esc_attr( $booking['id'] ) . '" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_create_key' ) ) . '" />';
            echo '<button class="loft1325-primary" type="submit">Créer/Renvoyer clé</button>';
            echo '</form>';

            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-inline-form">';
            echo '<input type="hidden" name="action" value="loft1325_revoke_key" />';
            echo '<input type="hidden" name="booking_id" value="' . esc_attr( $booking['id'] ) . '" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_revoke_key' ) ) . '" />';
            echo '<button class="loft1325-secondary" type="submit">Révoquer</button>';
            echo '</form>';
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
        $check_in = isset( $_GET['check_in'] ) ? sanitize_text_field( wp_unslash( $_GET['check_in'] ) ) : current_time( 'Y-m-d' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $check_out = isset( $_GET['check_out'] ) ? sanitize_text_field( wp_unslash( $_GET['check_out'] ) ) : gmdate( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $loft_type = isset( $_GET['loft_type'] ) ? sanitize_key( wp_unslash( $_GET['loft_type'] ) ) : 'simple'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! in_array( $loft_type, array( 'simple', 'double', 'penthouse' ), true ) ) {
            $loft_type = 'simple';
        }

        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-form">';
        echo '<input type="hidden" name="action" value="loft1325_view_availability" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( 'loft1325_view_availability' ) ) . '" />';
        echo '<label>Check-in</label><input type="date" name="check_in" value="' . esc_attr( $check_in ) . '" required />';
        echo '<label>Check-out</label><input type="date" name="check_out" value="' . esc_attr( $check_out ) . '" required />';
        echo '<label>Type</label>';
        echo '<select name="loft_type"><option value="simple" ' . selected( $loft_type, 'simple', false ) . '>Simple</option><option value="double" ' . selected( $loft_type, 'double', false ) . '>Double</option><option value="penthouse" ' . selected( $loft_type, 'penthouse', false ) . '>Penthouse</option></select>';
        echo '<button type="submit" class="loft1325-primary">Voir disponibilités</button>';
        echo '</form>';
        if ( ! empty( $_GET['check_in'] ) && ! empty( $_GET['check_out'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $check_in_utc = gmdate( 'Y-m-d H:i:s', strtotime( $check_in . ' 00:00:00' ) );
            $check_out_utc = gmdate( 'Y-m-d H:i:s', strtotime( $check_out . ' 00:00:00' ) );
            $rows = Loft1325_Operations::get_loft_availability_for_range( $check_in_utc, $check_out_utc, $loft_type );
            $free_count = 0;
            foreach ( $rows as $row ) {
                if ( empty( $row['is_busy'] ) ) {
                    $free_count++;
                }
            }

            echo '<div class="loft1325-callout">';
            echo esc_html( sprintf( 'Résultats %s → %s · %s disponible(s): %d', $check_in, $check_out, ucfirst( $loft_type ), $free_count ) );
            echo '</div>';
        }
        echo '<div class="loft1325-callout">Les disponibilités sont calculées en temps réel avec un contrôle d\'overlap sécurisé.</div>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_calendar() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        try {
            $period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'today';
            $view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'bookings';
            $bookings = Loft1325_Operations::get_bookings_with_cleaning( $period );
            $tickets = Loft1325_Operations::get_maintenance_tickets();

        self::render_page_header( 'Booking Hub' );

        if ( ! empty( $_GET['loft1325_ops_error'] ) ) {
            echo '<div class="notice notice-error"><p>Une erreur est survenue pendant l&rsquo;action demandée (approbation/refus/ménage). Veuillez consulter le journal PHP pour le détail technique.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_conflict'] ) ) {
            echo '<div class="notice notice-warning"><p>Impossible de confirmer: ce loft est déjà occupé sur cette période.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_not_free'] ) ) {
            echo '<div class="notice notice-warning"><p>Le loft n&rsquo;est plus FREE sur cette période. Rafraîchissez la vue avant de continuer.</p></div>';
        }

        if ( ! empty( $_GET['loft1325_ops_free_confirmed'] ) ) {
            $confirmed_loft_id = isset( $_GET['loft1325_loft_id'] ) ? absint( $_GET['loft1325_loft_id'] ) : 0;
            $confirmed_label = $confirmed_loft_id ? sprintf( ' (ID #%d)', $confirmed_loft_id ) : '';
            echo '<div class="notice notice-success"><p>Disponibilité FREE confirmée' . esc_html( $confirmed_label ) . '.</p></div>';
        }

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
                        echo '<input type="hidden" name="loft1325_ops_action" value="confirm_free" />';
                        echo '<input type="hidden" name="loft_id" value="' . esc_attr( $row['id'] ) . '" />';
                        echo '<input type="hidden" name="period" value="' . esc_attr( $period ) . '" />';
                        echo '<button class="loft1325-secondary" type="submit">Confirmer FREE</button>';
                        echo '</form>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        }

            echo '</div>';
            echo '</div>';
        } catch ( Throwable $throwable ) {
            error_log(
                sprintf(
                    '[Loft1325 Booking Hub] Failed rendering calendar: %s in %s:%d',
                    $throwable->getMessage(),
                    $throwable->getFile(),
                    $throwable->getLine()
                )
            );

            self::render_page_header( 'Booking Hub' );
            echo '<div class="notice notice-error"><p>Une erreur est survenue lors du chargement du calendrier. Réessayez et vérifiez le journal d&rsquo;erreurs si le problème persiste.</p></div>';
            echo '</div>';
        }
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
        $clients = Loft1325_Bookings::get_clients();

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
        echo '<label>Client existant</label>';
        echo '<select id="loft1325-client-select"><option value="">Sélectionner un client…</option>';
        foreach ( $clients as $client ) {
            $payload = array(
                'name' => $client['full_name'],
                'email' => $client['email'],
                'phone' => $client['phone'],
            );
            echo '<option value="' . esc_attr( wp_json_encode( $payload ) ) . '">' . esc_html( $client['full_name'] . ' · ' . $client['email'] ) . '</option>';
        }
        echo '</select>';
        echo '<label>Nom</label><input type="text" name="guest_name" required />';
        echo '<label>Email</label><input type="email" name="guest_email" />';
        echo '<label>Téléphone</label><input type="text" name="guest_phone" />';

        echo '<h3>3. Notes</h3>';
        echo '<textarea name="notes" rows="3"></textarea>';

        echo '<label class="loft1325-toggle"><input type="checkbox" name="create_key" /> Créer clé ButterflyMX</label>';
        echo '<button class="loft1325-primary">FINALISER</button>';
        echo '</form>';
        echo '<script>(function(){var s=document.getElementById("loft1325-client-select");if(!s){return;}s.addEventListener("change",function(){if(!s.value){return;}try{var c=JSON.parse(s.value);var n=document.querySelector("input[name=\'guest_name\']");var e=document.querySelector("input[name=\'guest_email\']");var p=document.querySelector("input[name=\'guest_phone\']");if(n&&c.name){n.value=c.name;}if(e&&c.email){e.value=c.email;}if(p&&c.phone){p.value=c.phone;}}catch(err){}});})();</script>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_settings() {
        if ( self::render_locked_if_needed() ) {
            return;
        }

        $settings = loft1325_get_settings();
        $nonce = wp_create_nonce( 'loft1325_save_settings' );

        self::render_page_header( 'Paramètres' );

        if ( isset( $_GET['loft1325_discovery_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success"><p>Discovery audit completed.</p></div>';
        }

        if ( isset( $_GET['loft1325_categorization_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success"><p>Loft categorization completed and cached.</p></div>';
        }

        if ( isset( $_GET['loft1325_categorization_blocked'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-warning"><p>Categorization writes are restricted to staging environments.</p></div>';
        }

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
        echo '<button class="loft1325-primary">Enregistrer</button>';
        echo '</form>';
        echo '</div>';



        self::render_sync_tools_section();

        echo '<div class="loft1325-card">';
        echo '<h3>Accès public (lien externe)</h3>';
        echo '<p>Créez une page WordPress et ajoutez le shortcode suivant :</p>';
        echo '<code>[loft1325_booking_hub]</code><br/><code>[loft1325_cleaning_hub]</code><br/><code>[loft1325_maintenance_hub]</code>';
        echo '</div>';
        echo '</div>';
    }

    private static function is_staging_environment() {
        return defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE;
    }

    private static function can_run_write_actions() {
        return self::is_staging_environment();
    }

    public static function ajax_test_butterfly_connection() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_send_json_error( array( 'message' => __( 'Access denied.', 'loft1325-booking-hub' ) ), 403 );
        }

        check_ajax_referer( 'loft1325_test_butterfly_connection', 'nonce' );

        $settings = loft1325_get_settings();
        $params = array(
            'per_page' => 1,
        );

        if ( ! empty( $settings['building_id'] ) ) {
            $params['building_id'] = sanitize_text_field( (string) $settings['building_id'] );
        }

        // Use the same token-based flow already used by the booking hub sync methods.
        $response = Loft1325_API_ButterflyMX::list_keychains( $params );

        if ( is_wp_error( $response ) ) {
            error_log( '[Loft1325 Discovery] Test connection failed: ' . $response->get_error_message() );
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $status = wp_remote_retrieve_response_code( $response );
        if ( $status >= 200 && $status < 300 ) {
            wp_send_json_success( array( 'message' => __( 'ButterflyMX token connection successful.', 'loft1325-booking-hub' ) ) );
        }

        $body = wp_remote_retrieve_body( $response );
        error_log( '[Loft1325 Discovery] Test connection HTTP ' . $status . ' body: ' . $body );

        wp_send_json_error( array( 'message' => sprintf( __( 'ButterflyMX responded with HTTP %d.', 'loft1325-booking-hub' ), $status ) ) );
    }

    public static function handle_run_discovery_audit() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_run_discovery_audit' );

        self::run_discovery_audit();

        wp_safe_redirect( add_query_arg( array( 'page' => 'loft1325-settings', 'loft1325_discovery_done' => 1 ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public static function handle_run_loft_categorization() {
        if ( ! current_user_can( 'loft1325_manage_bookings' ) ) {
            wp_die( esc_html__( 'Access denied.', 'loft1325-booking-hub' ) );
        }

        check_admin_referer( 'loft1325_run_loft_categorization' );

        if ( ! self::can_run_write_actions() ) {
            wp_safe_redirect( add_query_arg( array( 'page' => 'loft1325-settings', 'loft1325_categorization_blocked' => 1 ), admin_url( 'admin.php' ) ) );
            exit;
        }

        self::run_loft_categorization( true );

        wp_safe_redirect( add_query_arg( array( 'page' => 'loft1325-settings', 'loft1325_categorization_done' => 1 ), admin_url( 'admin.php' ) ) );
        exit;
    }

    private static function maybe_create_wp_lofts_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = $wpdb->prefix . 'lofts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            loft_id varchar(191) NOT NULL,
            type enum('Resident','Rental') NOT NULL DEFAULT 'Rental',
            status enum('Free','Busy','Tentative') NOT NULL DEFAULT 'Free',
            butterfly_unit_id varchar(191) DEFAULT '',
            last_sync datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY loft_id (loft_id)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    private static function run_discovery_audit() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $active_plugins = (array) get_option( 'active_plugins', array() );
        $patterns = array( 'butterflymx', 'wp_remote_get', 'api.butterflymx.com', 'keychains', 'tenants', 'visitor passes', '/v4/tenants', '/v4/keychains' );
        $findings = array();

        foreach ( $active_plugins as $plugin_file ) {
            $base_path = trailingslashit( WP_PLUGIN_DIR ) . dirname( $plugin_file );
            if ( ! is_dir( $base_path ) ) {
                $base_path = trailingslashit( WP_PLUGIN_DIR ) . $plugin_file;
            }

            $files = array();
            if ( is_dir( $base_path ) ) {
                $iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base_path ) );
                foreach ( $iterator as $file ) {
                    if ( $file instanceof SplFileInfo ) {
                        $files[] = $file->getPathname();
                    }
                }
            } elseif ( is_file( $base_path ) ) {
                $files[] = $base_path;
            }

            foreach ( $files as $file_path ) {
                if ( ! is_readable( $file_path ) || '.php' !== substr( $file_path, -4 ) ) {
                    continue;
                }

                $content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                if ( false === $content ) {
                    continue;
                }

                foreach ( $patterns as $pattern ) {
                    if ( false !== stripos( $content, $pattern ) ) {
                        $findings[] = array(
                            'plugin' => $plugin_file,
                            'file' => str_replace( trailingslashit( ABSPATH ), '', $file_path ),
                            'pattern' => $pattern,
                        );
                    }
                }
            }
        }

        global $wpdb;
        $loft_tables = $wpdb->get_col( "SHOW TABLES LIKE '%loft%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
        $booking_tables = $wpdb->get_col( "SHOW TABLES LIKE '%booking%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

        $report = array(
            'ran_at' => current_time( 'mysql' ),
            'environment' => defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : 'not-set',
            'active_plugins' => $active_plugins,
            'findings' => $findings,
            'loft_tables' => $loft_tables,
            'booking_tables' => $booking_tables,
            'recommendations' => in_array( $wpdb->prefix . 'lofts', $loft_tables, true ) ? array() : array( $wpdb->prefix . 'lofts' ),
        );

        update_option( 'loft1325_discovery_audit_report', $report, false );
        error_log( '[Loft1325 Discovery] Audit completed.' );

        return $report;
    }

    private static function run_loft_categorization( $force_refresh = false ) {
        if ( ! $force_refresh ) {
            $cached = get_transient( 'loft_categorization_cache' );
            if ( is_array( $cached ) ) {
                return $cached;
            }
        }

        self::maybe_create_wp_lofts_table();

        $tenants_response = Loft1325_API_ButterflyMX::list_tenants();
        $keychains_response = Loft1325_API_ButterflyMX::list_keychains();

        if ( is_wp_error( $tenants_response ) || is_wp_error( $keychains_response ) ) {
            $error = is_wp_error( $tenants_response ) ? $tenants_response->get_error_message() : $keychains_response->get_error_message();
            error_log( '[Loft1325 Discovery] Categorization failed: ' . $error );
            return array();
        }

        $tenants_body = json_decode( wp_remote_retrieve_body( $tenants_response ), true );
        $keychains_body = json_decode( wp_remote_retrieve_body( $keychains_response ), true );
        $tenants = isset( $tenants_body['data'] ) && is_array( $tenants_body['data'] ) ? $tenants_body['data'] : array();
        $keychains = isset( $keychains_body['data'] ) && is_array( $keychains_body['data'] ) ? $keychains_body['data'] : array();

        global $wpdb;
        $table_name = $wpdb->prefix . 'lofts';
        $now = current_time( 'mysql' );
        $results = array();

        foreach ( $tenants as $tenant ) {
            $tenant_id = isset( $tenant['id'] ) ? (string) $tenant['id'] : '';
            $attributes = isset( $tenant['attributes'] ) && is_array( $tenant['attributes'] ) ? $tenant['attributes'] : array();
            $name = isset( $attributes['name'] ) ? (string) $attributes['name'] : '';
            $expires_at = isset( $attributes['expiration_date'] ) ? (string) $attributes['expiration_date'] : '';
            $unit_id = isset( $attributes['unit_id'] ) ? (string) $attributes['unit_id'] : '';

            $is_permanent = '' === $expires_at || ! empty( $attributes['is_permanent'] );
            $has_temp_name = (bool) preg_match( '/\d{4}-\d{2}-\d{2}|PLETHORA|GUEST|TEMP/i', $name );
            $has_temp_keychain = false;

            foreach ( $keychains as $keychain ) {
                $keychain_attributes = isset( $keychain['attributes'] ) && is_array( $keychain['attributes'] ) ? $keychain['attributes'] : array();
                $keychain_tenant_id = isset( $keychain_attributes['tenant_id'] ) ? (string) $keychain_attributes['tenant_id'] : '';
                $keychain_type = isset( $keychain_attributes['type'] ) ? (string) $keychain_attributes['type'] : '';

                if ( $keychain_tenant_id === $tenant_id && in_array( $keychain_type, array( 'custom', 'recurring', 'one_time' ), true ) ) {
                    $has_temp_keychain = true;
                    break;
                }
            }

            $type = ( $is_permanent && ! $has_temp_name && ! $has_temp_keychain ) ? 'Resident' : 'Rental';
            $status = ! empty( $attributes['active'] ) ? 'Busy' : 'Free';

            if ( '' !== $tenant_id ) {
                $wpdb->replace(
                    $table_name,
                    array(
                        'loft_id' => $tenant_id,
                        'type' => $type,
                        'status' => $status,
                        'butterfly_unit_id' => $unit_id,
                        'last_sync' => $now,
                    ),
                    array( '%s', '%s', '%s', '%s', '%s' )
                );
            }

            $results[] = array(
                'loft_id' => $tenant_id,
                'type' => $type,
                'status' => $status,
                'butterfly_unit_id' => $unit_id,
                'last_sync' => $now,
            );
        }

        set_transient( 'loft_categorization_cache', $results, HOUR_IN_SECONDS );
        update_option( 'loft1325_loft_categorization_results', $results, false );
        error_log( '[Loft1325 Discovery] Categorization completed. Rows: ' . count( $results ) );

        return $results;
    }

    private static function render_sync_tools_section() {
        $discovery_report = get_option( 'loft1325_discovery_audit_report', array() );
        $categorization_results = get_option( 'loft1325_loft_categorization_results', array() );

        echo '<div class="loft1325-card">';
        echo '<h3>Connectivity & Discovery</h3>';
        echo '<p class="loft1325-meta">Use existing ButterflyMX credentials already configured above.</p>';
        echo '<p><button type="button" class="loft1325-secondary" id="loft1325-test-connection">Test Connection</button> <span id="loft1325-test-connection-result" class="loft1325-meta"></span></p>';

        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-inline-form" style="margin-bottom:8px;">';
        wp_nonce_field( 'loft1325_run_discovery_audit' );
        echo '<input type="hidden" name="action" value="loft1325_run_discovery_audit" />';
        echo '<button type="submit" class="loft1325-secondary">Run Discovery Audit</button>';
        echo '</form>';

        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loft1325-inline-form">';
        wp_nonce_field( 'loft1325_run_loft_categorization' );
        echo '<input type="hidden" name="action" value="loft1325_run_loft_categorization" />';
        echo '<button type="submit" class="loft1325-secondary">Run Loft Categorization</button>';
        echo '</form>';

        if ( ! empty( $discovery_report['ran_at'] ) ) {
            echo '<p class="loft1325-meta" style="margin-top:12px;">Last audit: ' . esc_html( $discovery_report['ran_at'] ) . ' (' . esc_html( $discovery_report['environment'] ?? 'n/a' ) . ')</p>';
        }

        if ( ! empty( $categorization_results ) ) {
            echo '<table class="widefat striped" style="margin-top:12px;">';
            echo '<thead><tr><th>Loft ID</th><th>Type</th><th>Status</th><th>Unit ID</th><th>Last sync</th></tr></thead><tbody>';
            foreach ( array_slice( $categorization_results, 0, 20 ) as $row ) {
                echo '<tr>';
                echo '<td>' . esc_html( $row['loft_id'] ?? '' ) . '</td>';
                echo '<td>' . esc_html( $row['type'] ?? '' ) . '</td>';
                echo '<td>' . esc_html( $row['status'] ?? '' ) . '</td>';
                echo '<td>' . esc_html( $row['butterfly_unit_id'] ?? '' ) . '</td>';
                echo '<td>' . esc_html( $row['last_sync'] ?? '' ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        $nonce = wp_create_nonce( 'loft1325_test_butterfly_connection' );
        echo '<script>jQuery(function($){$("#loft1325-test-connection").on("click", function(){var $r=$("#loft1325-test-connection-result");$r.text("Testing...");$.post(ajaxurl,{action:"loft1325_test_butterfly_connection",nonce:"' . esc_js( $nonce ) . '"}).done(function(resp){$r.text(resp&&resp.data&&resp.data.message?resp.data.message:"Done");}).fail(function(){ $r.text("Request failed"); });});});</script>';

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
