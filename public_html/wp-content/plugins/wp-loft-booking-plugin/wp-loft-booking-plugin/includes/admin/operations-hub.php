<?php
defined('ABSPATH') || exit;

function wp_loft_ops_get_settings() {
    $defaults = [
        'admin_password' => '',
        'cleaning_password' => '',
        'maintenance_password' => '',
        'admin_emails' => get_option('admin_email', ''),
        'cleaning_emails' => '',
        'maintenance_emails' => '',
    ];

    $settings = get_option('wp_loft_ops_settings', []);
    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args($settings, $defaults);
}

function wp_loft_ops_parse_emails($value) {
    $emails = preg_split('/[\s,;]+/', (string) $value);
    $emails = array_filter(array_map('sanitize_email', $emails), 'is_email');
    return array_values(array_unique($emails));
}

function wp_loft_ops_update_booking_status($booking_id, $status) {
    global $wpdb;
    $allowed = ['Pending', 'Confirmed', 'Cancelled'];

    if (!in_array($status, $allowed, true)) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'loft_bookings',
        ['booking_status' => $status],
        ['id' => absint($booking_id)],
        ['%s'],
        ['%d']
    );
}

function wp_loft_ops_update_cleaning_status($booking_id, $status) {
    global $wpdb;
    $allowed = ['pending', 'in_progress', 'ready', 'issue'];

    if (!in_array($status, $allowed, true)) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'loft_bookings',
        ['cleaning_status' => $status],
        ['id' => absint($booking_id)],
        ['%s'],
        ['%d']
    );
}

function wp_loft_ops_get_period_bounds($period) {
    $now = current_time('timestamp');
    $start = strtotime('today', $now);

    switch ($period) {
        case 'week':
            $end = strtotime('+7 days', $start);
            break;
        case 'month':
            $end = strtotime('+1 month', $start);
            break;
        case 'year':
            $end = strtotime('+1 year', $start);
            break;
        default:
            $period = 'today';
            $end = strtotime('+1 day', $start);
            break;
    }

    return [
        'period' => $period,
        'start' => gmdate('Y-m-d H:i:s', $start + (get_option('gmt_offset') * HOUR_IN_SECONDS)),
        'end' => gmdate('Y-m-d H:i:s', $end + (get_option('gmt_offset') * HOUR_IN_SECONDS)),
    ];
}

function wp_loft_ops_fetch_bookings($period = 'today') {
    global $wpdb;
    $bounds = wp_loft_ops_get_period_bounds($period);

    return $wpdb->get_results($wpdb->prepare(
        "SELECT b.*, u.unit_name
        FROM {$wpdb->prefix}loft_bookings b
        LEFT JOIN {$wpdb->prefix}loft_units u ON u.id = b.unit_id
        WHERE b.checkin_date < %s AND b.checkout_date >= %s
        ORDER BY b.checkin_date ASC",
        $bounds['end'],
        $bounds['start']
    ));
}

function wp_loft_ops_render_period_filters($base_url, $period) {
    $labels = [
        'today' => 'Today',
        'week' => 'Week',
        'month' => 'Month',
        'year' => 'Year',
    ];

    echo '<p style="display:flex;gap:8px;flex-wrap:wrap;">';
    foreach ($labels as $key => $label) {
        $class = $period === $key ? 'button button-primary' : 'button';
        printf('<a class="%s" href="%s">%s</a>', esc_attr($class), esc_url(add_query_arg('period', $key, $base_url)), esc_html($label));
    }
    echo '</p>';
}

function wp_loft_ops_render_table($bookings, $show_actions = true, $mode = 'bookings') {
    if (empty($bookings)) {
        echo '<p>No records found for this period.</p>';
        return;
    }

    echo '<table class="widefat striped"><thead><tr><th>Loft</th><th>Guest</th><th>Dates</th><th>Booking</th><th>Cleaning</th>';
    if ($show_actions) {
        echo '<th>Actions</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($bookings as $booking) {
        $guest = trim(($booking->customer_name ?? '') . ' ' . ($booking->customer_email ? "({$booking->customer_email})" : ''));
        echo '<tr>';
        echo '<td>' . esc_html($booking->unit_name ?: ('#' . $booking->unit_id)) . '</td>';
        echo '<td>' . esc_html($guest) . '</td>';
        echo '<td>' . esc_html($booking->checkin_date . ' → ' . $booking->checkout_date) . '</td>';
        echo '<td>' . esc_html($booking->booking_status ?: 'Pending') . '</td>';
        echo '<td>' . esc_html($booking->cleaning_status ?: 'pending') . '</td>';

        if ($show_actions) {
            echo '<td><form method="post" style="display:flex;gap:6px;flex-wrap:wrap;">';
            wp_nonce_field('wp_loft_ops_action', 'wp_loft_ops_nonce');
            echo '<input type="hidden" name="booking_id" value="' . esc_attr($booking->id) . '">';

            if ($mode === 'bookings') {
                echo '<button class="button button-primary" name="ops_action" value="approve">Approve</button>';
                echo '<button class="button" name="ops_action" value="reject">Reject</button>';
            } elseif ($mode === 'cleaning') {
                echo '<button class="button" name="ops_action" value="dirty">Dirty</button>';
                echo '<button class="button" name="ops_action" value="in_progress">In progress</button>';
                echo '<button class="button button-primary" name="ops_action" value="cleaned">Cleaned</button>';
                echo '<button class="button" name="ops_action" value="issue">Needs maintenance</button>';
            }

            echo '</form></td>';
        }

        echo '</tr>';
    }

    echo '</tbody></table>';
}

function wp_loft_ops_handle_admin_post() {
    if (empty($_POST['ops_action']) || !check_admin_referer('wp_loft_ops_action', 'wp_loft_ops_nonce')) {
        return;
    }

    $booking_id = absint($_POST['booking_id'] ?? 0);
    $action = sanitize_key($_POST['ops_action']);

    if (!$booking_id) {
        return;
    }

    if ($action === 'approve') {
        wp_loft_ops_update_booking_status($booking_id, 'Confirmed');
    } elseif ($action === 'reject') {
        wp_loft_ops_update_booking_status($booking_id, 'Cancelled');
    } elseif ($action === 'dirty') {
        wp_loft_ops_update_cleaning_status($booking_id, 'pending');
    } elseif ($action === 'in_progress') {
        wp_loft_ops_update_cleaning_status($booking_id, 'in_progress');
    } elseif ($action === 'cleaned') {
        wp_loft_ops_update_cleaning_status($booking_id, 'ready');
    } elseif ($action === 'issue') {
        wp_loft_ops_update_cleaning_status($booking_id, 'issue');
    }
}
add_action('admin_init', 'wp_loft_ops_handle_admin_post');
add_action('init', 'wp_loft_ops_handle_admin_post');

function wp_loft_ops_handle_settings_post() {
    if (empty($_POST['wp_loft_ops_save_settings']) || !check_admin_referer('wp_loft_ops_save_settings')) {
        return;
    }

    $settings = wp_loft_ops_get_settings();
    $settings['admin_password'] = sanitize_text_field(wp_unslash($_POST['admin_password'] ?? ''));
    $settings['cleaning_password'] = sanitize_text_field(wp_unslash($_POST['cleaning_password'] ?? ''));
    $settings['maintenance_password'] = sanitize_text_field(wp_unslash($_POST['maintenance_password'] ?? ''));
    $settings['admin_emails'] = sanitize_textarea_field(wp_unslash($_POST['admin_emails'] ?? ''));
    $settings['cleaning_emails'] = sanitize_textarea_field(wp_unslash($_POST['cleaning_emails'] ?? ''));
    $settings['maintenance_emails'] = sanitize_textarea_field(wp_unslash($_POST['maintenance_emails'] ?? ''));
    update_option('wp_loft_ops_settings', $settings);

    wp_safe_redirect(add_query_arg('ops_saved', '1', wp_get_referer() ?: admin_url('admin.php?page=loft-booking-google-calendar')));
    exit;
}
add_action('admin_init', 'wp_loft_ops_handle_settings_post');

function wp_loft_ops_maintenance_handle_post() {
    global $wpdb;

    if (!empty($_POST['wp_loft_add_maintenance']) && check_admin_referer('wp_loft_add_maintenance')) {
        $wpdb->insert(
            $wpdb->prefix . 'loft_maintenance_tasks',
            [
                'loft_label' => sanitize_text_field(wp_unslash($_POST['loft_label'] ?? '')),
                'title' => sanitize_text_field(wp_unslash($_POST['title'] ?? '')),
                'details' => sanitize_textarea_field(wp_unslash($_POST['details'] ?? '')),
                'priority' => sanitize_key($_POST['priority'] ?? 'normal'),
                'status' => 'todo',
                'assignee_email' => sanitize_email(wp_unslash($_POST['assignee_email'] ?? '')),
                'requested_by_email' => sanitize_email(wp_unslash($_POST['requested_by_email'] ?? '')),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s','%s','%s','%s','%s','%s','%s','%s','%s']
        );

        $settings = wp_loft_ops_get_settings();
        $notify = wp_loft_ops_parse_emails($settings['maintenance_emails']);
        $assignee = sanitize_email(wp_unslash($_POST['assignee_email'] ?? ''));
        if (is_email($assignee)) {
            $notify[] = $assignee;
        }
        $notify = array_values(array_unique(array_filter($notify, 'is_email')));

        if (!empty($notify)) {
            wp_mail($notify, 'New Loft Maintenance Ticket', sanitize_textarea_field(wp_unslash($_POST['details'] ?? '')));
        }
    }

    if (!empty($_POST['wp_loft_update_ticket']) && check_admin_referer('wp_loft_update_ticket')) {
        $wpdb->update(
            $wpdb->prefix . 'loft_maintenance_tasks',
            [
                'status' => sanitize_key($_POST['status'] ?? 'todo'),
                'updated_at' => current_time('mysql'),
            ],
            ['id' => absint($_POST['ticket_id'] ?? 0)],
            ['%s', '%s'],
            ['%d']
        );
    }
}
add_action('admin_init', 'wp_loft_ops_maintenance_handle_post');

function wp_loft_operations_hub_render_inner($base_slug = 'loft-booking-google-calendar') {
    global $wpdb;

    $view = sanitize_key($_GET['view'] ?? 'bookings');
    $period = sanitize_key($_GET['period'] ?? 'today');
    $bookings = wp_loft_ops_fetch_bookings($period);
    $settings = wp_loft_ops_get_settings();

    if (!empty($_GET['ops_saved'])) {
        echo '<div class="notice notice-success"><p>Operations settings saved.</p></div>';
    }

    echo '<p style="display:flex;gap:8px;">';
    foreach (['bookings' => 'Booking approvals', 'cleaning' => 'Cleaning board', 'maintenance' => 'Maintenance tickets', 'settings' => 'Hub settings'] as $key => $label) {
        $class = $view === $key ? 'button button-primary' : 'button';
        echo '<a class="' . esc_attr($class) . '" href="' . esc_url(add_query_arg('view', $key, admin_url('admin.php?page=' . $base_slug))) . '">' . esc_html($label) . '</a>';
    }
    echo '</p>';

    if ($view === 'bookings') {
        wp_loft_ops_render_period_filters(admin_url('admin.php?page=' . $base_slug . '&view=bookings'), $period);
        wp_loft_ops_render_table($bookings, true, 'bookings');
    } elseif ($view === 'cleaning') {
        wp_loft_ops_render_period_filters(admin_url('admin.php?page=' . $base_slug . '&view=cleaning'), $period);
        wp_loft_ops_render_table($bookings, true, 'cleaning');
    } elseif ($view === 'maintenance') {
        $tickets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}loft_maintenance_tasks ORDER BY updated_at DESC LIMIT 100");

        echo '<h2>Create maintenance ticket</h2><form method="post">';
        wp_nonce_field('wp_loft_add_maintenance');
        echo '<input type="hidden" name="wp_loft_add_maintenance" value="1">';
        echo '<p><input class="regular-text" name="loft_label" placeholder="Loft / Unit" required> ';
        echo '<input class="regular-text" name="title" placeholder="Issue title" required></p>';
        echo '<p><textarea class="large-text" rows="4" name="details" placeholder="Issue details" required></textarea></p>';
        echo '<p><select name="priority"><option value="critical">Critical</option><option value="urgent">Urgent</option><option value="normal" selected>Normal</option><option value="low">Low</option></select> ';
        echo '<input class="regular-text" type="email" name="assignee_email" placeholder="Assignee email"> ';
        echo '<input class="regular-text" type="email" name="requested_by_email" placeholder="Requested by (client email)"></p>';
        echo '<p><button class="button button-primary">Create ticket</button></p></form>';

        echo '<h2>Open tickets</h2>';
        if (empty($tickets)) {
            echo '<p>No maintenance tickets yet.</p>';
        } else {
            echo '<table class="widefat striped"><thead><tr><th>Loft</th><th>Title</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Actions</th></tr></thead><tbody>';
            foreach ($tickets as $ticket) {
                echo '<tr><td>' . esc_html($ticket->loft_label) . '</td><td><strong>' . esc_html($ticket->title) . '</strong><br><small>' . esc_html($ticket->details) . '</small></td><td>' . esc_html($ticket->priority) . '</td><td>' . esc_html($ticket->status) . '</td><td>' . esc_html($ticket->assignee_email) . '</td><td>';
                echo '<form method="post" style="display:flex;gap:6px;">';
                wp_nonce_field('wp_loft_update_ticket');
                echo '<input type="hidden" name="wp_loft_update_ticket" value="1"><input type="hidden" name="ticket_id" value="' . esc_attr($ticket->id) . '">';
                echo '<select name="status"><option value="todo">Todo</option><option value="in_progress">In progress</option><option value="done">Done</option></select>';
                echo '<button class="button">Update</button></form>';
                echo '</td></tr>';
            }
            echo '</tbody></table>';
        }
    } else {
        echo '<h2>Password & email settings</h2><form method="post">';
        wp_nonce_field('wp_loft_ops_save_settings');
        echo '<input type="hidden" name="wp_loft_ops_save_settings" value="1">';
        echo '<table class="form-table"><tr><th>Admin hub password</th><td><input class="regular-text" name="admin_password" value="' . esc_attr($settings['admin_password']) . '"></td></tr>';
        echo '<tr><th>Cleaning hub password</th><td><input class="regular-text" name="cleaning_password" value="' . esc_attr($settings['cleaning_password']) . '"></td></tr>';
        echo '<tr><th>Maintenance hub password</th><td><input class="regular-text" name="maintenance_password" value="' . esc_attr($settings['maintenance_password']) . '"></td></tr>';
        echo '<tr><th>Admin notification emails</th><td><textarea class="large-text" rows="3" name="admin_emails">' . esc_textarea($settings['admin_emails']) . '</textarea></td></tr>';
        echo '<tr><th>Cleaning notification emails</th><td><textarea class="large-text" rows="3" name="cleaning_emails">' . esc_textarea($settings['cleaning_emails']) . '</textarea></td></tr>';
        echo '<tr><th>Maintenance notification emails</th><td><textarea class="large-text" rows="3" name="maintenance_emails">' . esc_textarea($settings['maintenance_emails']) . '</textarea></td></tr></table>';
        echo '<p><button class="button button-primary">Save settings</button></p></form>';

        echo '<h3>Shortcodes</h3><ul><li>[loft_admin_hub]</li><li>[loft_cleaning_hub]</li><li>[loft_maintenance_hub]</li></ul>';
    }

}

function wp_loft_operations_hub_page() {
    echo '<div class="wrap"><h1>Operations Hub</h1>';
    wp_loft_operations_hub_render_inner('wp_loft_operations_hub');
    echo '</div>';
}

function wp_loft_ops_render_password_gate($scope, $content_cb) {
    $settings = wp_loft_ops_get_settings();
    $password_key = $scope . '_password';
    $password = (string) ($settings[$password_key] ?? '');

    if ($password === '') {
        ob_start();
        call_user_func($content_cb);
        return ob_get_clean();
    }

    $cookie_name = 'wp_loft_ops_' . $scope;
    $token = hash('sha256', $password . wp_salt('auth'));

    if (!empty($_POST['wp_loft_ops_scope']) && $_POST['wp_loft_ops_scope'] === $scope) {
        $submitted = sanitize_text_field(wp_unslash($_POST['wp_loft_ops_password'] ?? ''));
        if (hash_equals($password, $submitted)) {
            setcookie($cookie_name, $token, time() + DAY_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
            $_COOKIE[$cookie_name] = $token;
        }
    }

    if (($_COOKIE[$cookie_name] ?? '') !== $token) {
        ob_start();
        echo '<form method="post"><p><strong>Enter password</strong></p><p><input type="password" name="wp_loft_ops_password" required></p>';
        echo '<input type="hidden" name="wp_loft_ops_scope" value="' . esc_attr($scope) . '"><p><button>Access</button></p></form>';
        return ob_get_clean();
    }

    ob_start();
    call_user_func($content_cb);
    return ob_get_clean();
}

function wp_loft_ops_shortcode_admin_hub() {
    return wp_loft_ops_render_password_gate('admin', function () {
        $bookings = wp_loft_ops_fetch_bookings('week');
        wp_loft_ops_render_table($bookings, true, 'bookings');
    });
}
add_shortcode('loft_admin_hub', 'wp_loft_ops_shortcode_admin_hub');

function wp_loft_ops_shortcode_cleaning_hub() {
    return wp_loft_ops_render_password_gate('cleaning', function () {
        $bookings = wp_loft_ops_fetch_bookings('today');
        wp_loft_ops_render_table($bookings, true, 'cleaning');
    });
}
add_shortcode('loft_cleaning_hub', 'wp_loft_ops_shortcode_cleaning_hub');

function wp_loft_ops_shortcode_maintenance_hub() {
    global $wpdb;

    return wp_loft_ops_render_password_gate('maintenance', function () use ($wpdb) {
        $tickets = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}loft_maintenance_tasks ORDER BY updated_at DESC LIMIT 50");
        if (empty($tickets)) {
            echo '<p>No maintenance tickets.</p>';
            return;
        }

        echo '<ul>';
        foreach ($tickets as $ticket) {
            echo '<li><strong>' . esc_html($ticket->title) . '</strong> — ' . esc_html($ticket->status) . ' (' . esc_html($ticket->priority) . ') - ' . esc_html($ticket->loft_label) . '</li>';
        }
        echo '</ul>';
    });
}
add_shortcode('loft_maintenance_hub', 'wp_loft_ops_shortcode_maintenance_hub');

function wp_loft_ops_send_two_hour_cleaning_alerts() {
    global $wpdb;

    $settings = wp_loft_ops_get_settings();
    $admins = wp_loft_ops_parse_emails($settings['admin_emails']);
    $cleaning = wp_loft_ops_parse_emails($settings['cleaning_emails']);
    $recipients = array_values(array_unique(array_merge($admins, $cleaning)));

    if (empty($recipients)) {
        return;
    }

    $now = current_time('mysql');
    $future = gmdate('Y-m-d H:i:s', current_time('timestamp') + 2 * HOUR_IN_SECONDS + (get_option('gmt_offset') * HOUR_IN_SECONDS));

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT b.id, b.checkout_date, b.cleaning_status, u.unit_name
         FROM {$wpdb->prefix}loft_bookings b
         LEFT JOIN {$wpdb->prefix}loft_units u ON u.id = b.unit_id
         WHERE b.checkout_date BETWEEN %s AND %s
         AND b.cleaning_status <> 'ready'",
        $now,
        $future
    ));

    if (empty($rows)) {
        return;
    }

    $sent = get_option('wp_loft_ops_sent_alerts', []);
    if (!is_array($sent)) {
        $sent = [];
    }

    foreach ($rows as $row) {
        if (!empty($sent[$row->id])) {
            continue;
        }

        wp_mail(
            $recipients,
            'Loft cleaning window alert (2h)',
            sprintf('Loft %s checks out at %s and is not cleaned yet (status: %s).', $row->unit_name ?: ('#' . $row->id), $row->checkout_date, $row->cleaning_status ?: 'pending')
        );
        $sent[$row->id] = current_time('mysql');
    }

    update_option('wp_loft_ops_sent_alerts', $sent);
}
add_action('wp_loft_ops_two_hour_alert', 'wp_loft_ops_send_two_hour_cleaning_alerts');
