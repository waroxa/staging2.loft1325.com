<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_Operations {
    public static function boot() {
        add_action( 'init', array( __CLASS__, 'handle_post_actions' ) );
        add_action( 'init', array( __CLASS__, 'ensure_schedule' ) );
        add_action( 'loft1325_ops_two_hour_alert', array( __CLASS__, 'send_two_hour_cleaning_alerts' ) );
    }

    public static function ensure_schedule() {
        if ( ! wp_next_scheduled( 'loft1325_ops_two_hour_alert' ) ) {
            wp_schedule_event( time(), 'hourly', 'loft1325_ops_two_hour_alert' );
        }
    }

    public static function handle_post_actions() {
        if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['loft1325_ops_action'] ) ) {
            return;
        }

        $redirect = wp_get_referer();
        if ( ! $redirect ) {
            $redirect = admin_url( 'admin.php?page=loft1325-calendar' );
        }

        if ( ! is_user_logged_in() || ! current_user_can( 'loft1325_manage_bookings' ) ) {
            $login_url = wp_login_url( $redirect );
            wp_safe_redirect( add_query_arg( 'loft1325_ops_error', '1', $login_url ) );
            exit;
        }

        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'loft1325_ops_action' ) ) {
            wp_safe_redirect( add_query_arg( 'loft1325_ops_error', '1', $redirect ) );
            exit;
        }

        $action = sanitize_key( wp_unslash( $_POST['loft1325_ops_action'] ) );
        $booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
        $allowed_actions = array( 'approve', 'reject', 'dirty', 'in_progress', 'cleaned', 'issue', 'maintenance_create', 'maintenance_update', 'confirm_free' );

        if ( ! in_array( $action, $allowed_actions, true ) ) {
            wp_safe_redirect( add_query_arg( 'loft1325_ops_error', '1', $redirect ) );
            exit;
        }

        try {
            if ( 'approve' === $action && $booking_id ) {
                if ( ! self::can_confirm_booking( $booking_id ) ) {
                    $redirect = add_query_arg( 'loft1325_ops_conflict', '1', $redirect );
                } else {
                    self::update_booking_status( $booking_id, 'confirmed' );
                }
            } elseif ( 'reject' === $action && $booking_id ) {
                self::update_booking_status( $booking_id, 'cancelled' );
            } elseif ( in_array( $action, array( 'dirty', 'in_progress', 'cleaned', 'issue' ), true ) && $booking_id ) {
                $map = array(
                    'dirty' => 'pending',
                    'in_progress' => 'in_progress',
                    'cleaned' => 'ready',
                    'issue' => 'issue',
                );
                self::update_cleaning_status( $booking_id, $map[ $action ] );
            } elseif ( 'maintenance_create' === $action ) {
                self::create_maintenance_ticket( $_POST );
            } elseif ( 'maintenance_update' === $action ) {
                $ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
                $status    = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'todo';
                self::update_maintenance_status( $ticket_id, $status );
            } elseif ( 'confirm_free' === $action ) {
                $loft_id = isset( $_POST['loft_id'] ) ? absint( $_POST['loft_id'] ) : 0;
                $period  = isset( $_POST['period'] ) ? sanitize_key( wp_unslash( $_POST['period'] ) ) : 'today';

                if ( ! $loft_id ) {
                    $redirect = add_query_arg( 'loft1325_ops_error', '1', $redirect );
                } elseif ( self::is_loft_free_for_period( $loft_id, $period ) ) {
                    $redirect = add_query_arg(
                        array(
                            'loft1325_ops_free_confirmed' => '1',
                            'loft1325_loft_id' => $loft_id,
                        ),
                        $redirect
                    );
                } else {
                    $redirect = add_query_arg( 'loft1325_ops_not_free', '1', $redirect );
                }
            }
        } catch ( Throwable $throwable ) {
            error_log(
                sprintf(
                    '[Loft1325 Booking Hub] Action "%s" failed: %s in %s:%d',
                    $action,
                    $throwable->getMessage(),
                    $throwable->getFile(),
                    $throwable->getLine()
                )
            );

            $redirect = add_query_arg( 'loft1325_ops_error', '1', $redirect );
        }

        wp_safe_redirect( $redirect );
        exit;
    }

    public static function get_period_bounds( $period ) {
        $period = sanitize_key( $period );
        $start_ts = strtotime( 'today', current_time( 'timestamp' ) );

        switch ( $period ) {
            case 'biweek':
                $end_ts = strtotime( '+14 days', $start_ts );
                break;
            case 'week':
                $end_ts = strtotime( '+7 days', $start_ts );
                break;
            case 'month':
                $end_ts = strtotime( '+1 month', $start_ts );
                break;
            case 'year':
                $end_ts = strtotime( '+1 year', $start_ts );
                break;
            default:
                $period = 'today';
                $end_ts = strtotime( '+1 day', $start_ts );
                break;
        }

        return array(
            'period' => $period,
            'start' => gmdate( 'Y-m-d H:i:s', $start_ts ),
            'end' => gmdate( 'Y-m-d H:i:s', $end_ts ),
        );
    }

    public static function get_bookings_with_cleaning( $period = 'today' ) {
        global $wpdb;

        $bounds = self::get_period_bounds( $period );
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $cleaning_table = $wpdb->prefix . 'loft1325_cleaning_status';

        $sql = $wpdb->prepare(
            "SELECT b.*, l.loft_name, l.loft_type, COALESCE(c.cleaning_status, 'pending') AS cleaning_status
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON b.loft_id = l.id
            LEFT JOIN {$cleaning_table} c ON c.booking_id = b.id
            WHERE b.check_in_utc < %s AND b.check_out_utc >= %s
            ORDER BY b.check_in_utc ASC",
            $bounds['end'],
            $bounds['start']
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    public static function update_booking_status( $booking_id, $status ) {
        global $wpdb;
        $allowed = array( 'tentative', 'confirmed', 'checked_in', 'checked_out', 'cancelled' );
        if ( ! in_array( $status, $allowed, true ) ) {
            return;
        }

        $wpdb->update(
            $wpdb->prefix . 'loft1325_bookings',
            array( 'status' => $status, 'updated_at' => current_time( 'mysql', 1 ) ),
            array( 'id' => absint( $booking_id ) ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    public static function can_confirm_booking( $booking_id ) {
        global $wpdb;

        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $booking = $wpdb->get_row(
            $wpdb->prepare( "SELECT id, loft_id, check_in_utc, check_out_utc FROM {$bookings_table} WHERE id = %d", absint( $booking_id ) ),
            ARRAY_A
        );

        if ( empty( $booking['id'] ) || empty( $booking['loft_id'] ) || empty( $booking['check_in_utc'] ) || empty( $booking['check_out_utc'] ) ) {
            return false;
        }

        $conflict_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                FROM {$bookings_table}
                WHERE id <> %d
                AND loft_id = %d
                AND status IN ('confirmed','checked_in')
                AND %s < check_out_utc
                AND %s > check_in_utc",
                absint( $booking['id'] ),
                absint( $booking['loft_id'] ),
                $booking['check_in_utc'],
                $booking['check_out_utc']
            )
        );

        return ( (int) $conflict_count ) === 0;
    }

    public static function get_loft_availability( $period = 'today' ) {
        global $wpdb;

        $bounds = self::get_period_bounds( $period );
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT l.id, l.loft_name, l.loft_type,
                    COALESCE(MAX(CASE WHEN b.id IS NULL THEN 0 ELSE 1 END), 0) AS is_busy,
                    COALESCE(MAX(CASE WHEN b.butterfly_keychain_id IS NULL THEN 0 ELSE 1 END), 0) AS has_key,
                    MIN(b.check_in_utc) AS next_check_in
                FROM {$lofts_table} l
                LEFT JOIN {$bookings_table} b
                    ON b.loft_id = l.id
                    AND b.status IN ('confirmed','checked_in')
                    AND b.check_in_utc < %s
                    AND b.check_out_utc >= %s
                WHERE l.is_active = 1
                GROUP BY l.id, l.loft_name, l.loft_type
                ORDER BY l.loft_name ASC",
                $bounds['end'],
                $bounds['start']
            ),
            ARRAY_A
        );

        return is_array( $rows ) ? $rows : array();
    }

    public static function is_loft_free_for_period( $loft_id, $period = 'today' ) {
        global $wpdb;

        $bounds = self::get_period_bounds( $period );
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';

        $conflicts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                FROM {$bookings_table}
                WHERE loft_id = %d
                AND status IN ('confirmed','checked_in')
                AND check_in_utc < %s
                AND check_out_utc >= %s",
                absint( $loft_id ),
                $bounds['end'],
                $bounds['start']
            )
        );

        return ( (int) $conflicts ) === 0;
    }

    public static function update_cleaning_status( $booking_id, $status ) {
        global $wpdb;
        $allowed = array( 'pending', 'in_progress', 'ready', 'issue' );
        if ( ! in_array( $status, $allowed, true ) ) {
            return;
        }

        $table = $wpdb->prefix . 'loft1325_cleaning_status';
        $now = current_time( 'mysql', 1 );
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE booking_id = %d", absint( $booking_id ) ) );

        if ( $exists ) {
            $wpdb->update( $table, array( 'cleaning_status' => $status, 'updated_at' => $now ), array( 'booking_id' => absint( $booking_id ) ), array( '%s', '%s' ), array( '%d' ) );
        } else {
            $wpdb->insert( $table, array( 'booking_id' => absint( $booking_id ), 'cleaning_status' => $status, 'updated_at' => $now ), array( '%d', '%s', '%s' ) );
        }
    }

    public static function get_maintenance_tickets() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}loft1325_maintenance_tasks ORDER BY updated_at DESC LIMIT 100", ARRAY_A );
    }

    public static function create_maintenance_ticket( $raw ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'loft1325_maintenance_tasks',
            array(
                'loft_label' => sanitize_text_field( wp_unslash( $raw['loft_label'] ?? '' ) ),
                'title' => sanitize_text_field( wp_unslash( $raw['title'] ?? '' ) ),
                'details' => sanitize_textarea_field( wp_unslash( $raw['details'] ?? '' ) ),
                'priority' => sanitize_key( wp_unslash( $raw['priority'] ?? 'normal' ) ),
                'status' => 'todo',
                'assignee_email' => sanitize_email( wp_unslash( $raw['assignee_email'] ?? '' ) ),
                'requested_by_email' => sanitize_email( wp_unslash( $raw['requested_by_email'] ?? '' ) ),
                'created_at' => current_time( 'mysql', 1 ),
                'updated_at' => current_time( 'mysql', 1 ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        $settings = loft1325_get_settings();
        $emails = self::parse_emails( $settings['maintenance_team_emails'] ?? '' );
        $assignee = sanitize_email( wp_unslash( $raw['assignee_email'] ?? '' ) );
        if ( is_email( $assignee ) ) {
            $emails[] = $assignee;
        }

        $emails = array_values( array_unique( array_filter( $emails, 'is_email' ) ) );
        if ( ! empty( $emails ) ) {
            wp_mail( $emails, 'Loft1325: New maintenance ticket', sanitize_textarea_field( wp_unslash( $raw['details'] ?? '' ) ) );
        }
    }

    public static function update_maintenance_status( $ticket_id, $status ) {
        global $wpdb;
        $allowed = array( 'todo', 'in_progress', 'done' );
        if ( ! $ticket_id || ! in_array( $status, $allowed, true ) ) {
            return;
        }

        $wpdb->update(
            $wpdb->prefix . 'loft1325_maintenance_tasks',
            array( 'status' => $status, 'updated_at' => current_time( 'mysql', 1 ) ),
            array( 'id' => absint( $ticket_id ) ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    public static function parse_emails( $value ) {
        $emails = preg_split( '/[\s,;]+/', (string) $value );
        $emails = array_filter( array_map( 'sanitize_email', $emails ), 'is_email' );
        return array_values( array_unique( $emails ) );
    }

    public static function send_two_hour_cleaning_alerts() {
        global $wpdb;

        $settings = loft1325_get_settings();
        $admin_emails = self::parse_emails( $settings['admin_alert_emails'] ?? '' );
        $cleaning_emails = self::parse_emails( $settings['cleaning_team_emails'] ?? '' );
        $recipients = array_values( array_unique( array_merge( $admin_emails, $cleaning_emails ) ) );
        if ( empty( $recipients ) ) {
            return;
        }

        $now = gmdate( 'Y-m-d H:i:s' );
        $in_two_hours = gmdate( 'Y-m-d H:i:s', time() + ( 2 * HOUR_IN_SECONDS ) );
        $bookings_table = $wpdb->prefix . 'loft1325_bookings';
        $lofts_table = $wpdb->prefix . 'loft1325_lofts';
        $cleaning_table = $wpdb->prefix . 'loft1325_cleaning_status';

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT b.id, b.check_out_utc, l.loft_name, COALESCE(c.cleaning_status, 'pending') AS cleaning_status
            FROM {$bookings_table} b
            LEFT JOIN {$lofts_table} l ON l.id = b.loft_id
            LEFT JOIN {$cleaning_table} c ON c.booking_id = b.id
            WHERE b.check_out_utc BETWEEN %s AND %s
            AND b.status IN ('confirmed','checked_in')
            AND COALESCE(c.cleaning_status, 'pending') <> 'ready'",
            $now,
            $in_two_hours
        ), ARRAY_A );

        foreach ( $rows as $row ) {
            wp_mail(
                $recipients,
                'Loft1325 cleaning alert (2h window)',
                sprintf( 'Loft %s checks out at %s and cleaning is %s.', $row['loft_name'], $row['check_out_utc'], $row['cleaning_status'] )
            );
        }
    }
}
