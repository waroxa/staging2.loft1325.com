<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_API_ButterflyMX {
    public static function request( $method, $endpoint, $body = array() ) {
        $settings = loft1325_get_settings();
        $base_url = trailingslashit( $settings['api_base_url'] );
        $endpoint = ltrim( $endpoint, '/' );

        if ( str_ends_with( untrailingslashit( $base_url ), '/v4' ) && str_starts_with( $endpoint, 'v4/' ) ) {
            $endpoint = substr( $endpoint, 3 );
        }

        $url = $base_url . $endpoint;

        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            // TODO: Confirm ButterflyMX auth header format.
            'Authorization' => 'Bearer ' . $settings['api_token'],
        );

        $args = array(
            'method' => $method,
            'timeout' => 20,
            'headers' => $headers,
            'body' => empty( $body ) ? null : wp_json_encode( $body ),
        );

        $attempts = 0;
        $response = null;

        while ( $attempts < 3 ) {
            $attempts++;
            $response = wp_remote_request( $url, $args );

            if ( ! is_wp_error( $response ) ) {
                $code = wp_remote_retrieve_response_code( $response );
                if ( $code >= 200 && $code < 300 ) {
                    return $response;
                }
            }

            sleep( 1 );
        }

        return $response;
    }

    public static function create_keychain( $payload ) {
        $response = self::request( 'POST', '/v4/keychains/custom', $payload );
        loft1325_log_action( 'butterflymx_create_key', 'Create keychain request', array(
            'payload' => loft1325_redact_secrets( $payload ),
            'response' => loft1325_redact_secrets( $response ),
        ) );

        return $response;
    }

    public static function revoke_keychain( $keychain_id ) {
        $response = self::request( 'DELETE', '/v4/keychains/' . absint( $keychain_id ) );
        loft1325_log_action( 'butterflymx_revoke_key', 'Revoke keychain request', array(
            'payload' => array( 'keychain_id' => absint( $keychain_id ) ),
            'response' => loft1325_redact_secrets( $response ),
        ) );

        return $response;
    }

    public static function list_keychains( $params = array() ) {
        $query = '';
        if ( ! empty( $params ) ) {
            $query = '?' . http_build_query( $params );
        }
        $response = self::request( 'GET', '/v4/keychains' . $query );
        loft1325_log_action( 'butterflymx_list_keychains', 'List keychains request', array(
            'payload' => $params,
            'response' => loft1325_redact_secrets( $response ),
        ) );

        return $response;
    }
}
