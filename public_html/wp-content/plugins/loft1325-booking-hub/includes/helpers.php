<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function loft1325_get_settings() {
    $defaults = array(
        'api_base_url' => '',
        'api_token' => '',
        'default_access_point_ids' => '',
        'default_device_ids' => '',
        'building_timezone' => 'America/Montreal',
        'pass_naming_template' => 'GUEST|BOOKING:{booking_id}|{loft_name}|{guest_name}',
        'staff_prefix' => 'STAFF',
        'password_hash' => '',
    );

    $settings = get_option( LOFT1325_SETTINGS_OPTION, array() );
    $settings = wp_parse_args( $settings, $defaults );

    if ( empty( $settings['password_hash'] ) ) {
        $settings['password_hash'] = wp_hash_password( 'loft2026' );
        update_option( LOFT1325_SETTINGS_OPTION, $settings );
    }

    return $settings;
}

function loft1325_format_datetime_local( $utc_datetime ) {
    $settings = loft1325_get_settings();
    $timezone = new DateTimeZone( $settings['building_timezone'] );
    $utc = new DateTimeZone( 'UTC' );

    $date = new DateTime( $utc_datetime, $utc );
    $date->setTimezone( $timezone );

    return $date->format( 'Y-m-d H:i' );
}

function loft1325_to_utc( $datetime_string ) {
    $settings = loft1325_get_settings();
    $timezone = new DateTimeZone( $settings['building_timezone'] );
    $utc = new DateTimeZone( 'UTC' );

    $date = new DateTime( $datetime_string, $timezone );
    $date->setTimezone( $utc );

    return $date->format( 'Y-m-d H:i:s' );
}

function loft1325_sanitize_csv_ids( $value ) {
    $value = sanitize_text_field( $value );
    $ids = array_filter( array_map( 'trim', explode( ',', $value ) ) );
    $ids = array_map( 'absint', $ids );

    return implode( ',', array_filter( $ids ) );
}

function loft1325_log_action( $action, $message, $data = array() ) {
    global $wpdb;

    $table = $wpdb->prefix . 'loft1325_log';

    $wpdb->insert(
        $table,
        array(
            'booking_id' => isset( $data['booking_id'] ) ? absint( $data['booking_id'] ) : null,
            'loft_id' => isset( $data['loft_id'] ) ? absint( $data['loft_id'] ) : null,
            'user_id' => get_current_user_id(),
            'action' => sanitize_text_field( $action ),
            'message' => wp_kses_post( $message ),
            'payload' => isset( $data['payload'] ) ? wp_json_encode( $data['payload'] ) : null,
            'response' => isset( $data['response'] ) ? wp_json_encode( $data['response'] ) : null,
            'created_at' => current_time( 'mysql', 1 ),
        ),
        array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
    );
}

function loft1325_redact_secrets( $data ) {
    if ( empty( $data ) || ! is_array( $data ) ) {
        return $data;
    }

    $redacted = $data;
    if ( isset( $redacted['headers']['Authorization'] ) ) {
        $redacted['headers']['Authorization'] = 'REDACTED';
    }
    if ( isset( $redacted['headers']['X-API-Key'] ) ) {
        $redacted['headers']['X-API-Key'] = 'REDACTED';
    }

    return $redacted;
}
