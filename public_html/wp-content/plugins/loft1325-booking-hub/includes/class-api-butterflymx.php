<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_API_ButterflyMX {
    private static function get_environment() {
        $settings = loft1325_get_settings();
        $environment = isset( $settings['environment'] ) ? (string) $settings['environment'] : '';

        if ( ! in_array( $environment, array( 'sandbox', 'production' ), true ) ) {
            $environment = (string) get_option( 'butterflymx_environment', 'production' );
        }

        return ( 'sandbox' === $environment ) ? 'sandbox' : 'production';
    }

    private static function get_api_base_url( $version = 'v4' ) {
        $settings = loft1325_get_settings();
        $base_url = trailingslashit( (string) ( $settings['api_base_url'] ?? '' ) );

        if ( ! empty( $base_url ) ) {
            $base_url = untrailingslashit( $base_url );

            if ( 'v3' === $version && str_ends_with( $base_url, '/v4' ) ) {
                return substr( $base_url, 0, -3 ) . '/v3';
            }

            return $base_url;
        }

        $environment = self::get_environment();

        if ( 'v3' === $version ) {
            return ( 'sandbox' === $environment )
                ? 'https://api.na.sandbox.butterflymx.com/v3'
                : 'https://api.butterflymx.com/v3';
        }

        return ( 'sandbox' === $environment )
            ? 'https://api.na.sandbox.butterflymx.com/v4'
            : 'https://api.butterflymx.com/v4';
    }

    private static function maybe_request_oauth_token( $version = 'v4' ) {
        $settings = loft1325_get_settings();
        $client_id = isset( $settings['client_id'] ) ? (string) $settings['client_id'] : '';
        $client_secret = isset( $settings['client_secret'] ) ? (string) $settings['client_secret'] : '';

        if ( '' === $client_id ) {
            $client_id = (string) get_option( 'butterflymx_client_id', '' );
        }

        if ( '' === $client_secret ) {
            $client_secret = (string) get_option( 'butterflymx_client_secret', '' );
        }

        if ( '' === $client_id || '' === $client_secret ) {
            return '';
        }

        $expires_option = 'butterflymx_token_' . $version . '_expires';
        $existing_token = (string) get_option( 'butterflymx_access_token_' . $version, '' );
        if ( '' === $existing_token ) {
            $existing_token = (string) get_option( 'butterflymx_token_' . $version, '' );
        }

        $expires_at = absint( get_option( $expires_option, 0 ) );
        if ( '' !== $existing_token && $expires_at > time() + 60 ) {
            return $existing_token;
        }

        $oauth_url = ( 'sandbox' === self::get_environment() )
            ? 'https://accountssandbox.butterflymx.com/oauth/token'
            : 'https://accounts.butterflymx.com/oauth/token';

        $response = wp_remote_post(
            $oauth_url,
            array(
                'method' => 'POST',
                'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
                'body' => array(
                    'grant_type' => 'client_credentials',
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                ),
                'timeout' => 20,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $existing_token;
        }

        $status = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $status || ! is_array( $body ) || empty( $body['access_token'] ) ) {
            return $existing_token;
        }

        $token = (string) $body['access_token'];
        $expires_in = isset( $body['expires_in'] ) ? max( 60, absint( $body['expires_in'] ) ) : 3600;

        update_option( 'butterflymx_access_token_' . $version, $token );
        update_option( 'butterflymx_token_' . $version, $token );
        update_option( $expires_option, time() + $expires_in );

        return $token;
    }

    private static function get_token( $version = 'v4' ) {
        $settings = loft1325_get_settings();

        if ( 'v4' === $version && ! empty( $settings['api_token'] ) ) {
            return (string) $settings['api_token'];
        }

        if ( function_exists( 'get_butterflymx_access_token' ) ) {
            $token = (string) get_butterflymx_access_token( $version );
            if ( ! empty( $token ) ) {
                return $token;
            }
        }

        $token = (string) get_option( 'butterflymx_access_token_' . $version, '' );
        if ( ! empty( $token ) ) {
            return $token;
        }

        $token = (string) get_option( 'butterflymx_token_' . $version, '' );
        if ( ! empty( $token ) ) {
            return $token;
        }

        return self::maybe_request_oauth_token( $version );
    }

    public static function request( $method, $endpoint, $body = array() ) {
        return self::request_with_version( 'v4', $method, $endpoint, $body );
    }

    private static function request_with_version( $version, $method, $endpoint, $body = array() ) {
        $base_url = trailingslashit( self::get_api_base_url( $version ) );
        $endpoint = ltrim( $endpoint, '/' );
        $token = self::get_token( $version );

        if ( empty( $token ) ) {
            return new WP_Error( 'missing_token', 'Missing ButterflyMX API token for ' . $version . '.' );
        }

        if ( str_ends_with( untrailingslashit( $base_url ), '/v4' ) && str_starts_with( $endpoint, 'v4/' ) ) {
            $endpoint = substr( $endpoint, 3 );
        }
        if ( str_ends_with( untrailingslashit( $base_url ), '/v3' ) && str_starts_with( $endpoint, 'v3/' ) ) {
            $endpoint = substr( $endpoint, 3 );
        }

        $url = $base_url . $endpoint;

        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        );

        if ( 'v3' === $version ) {
            $headers['Content-Type'] = 'application/vnd.api+json';
            $headers['Accept'] = 'application/vnd.api+json';
        }

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
        $used_version = 'v4';
        if ( ! empty( $params ) ) {
            $query = '?' . http_build_query( $params );
        }
        $response = self::request_with_version( 'v4', 'GET', '/v4/keychains' . $query );

        $fallback_reason = '';
        if ( is_wp_error( $response ) ) {
            $fallback_reason = $response->get_error_message();
        } else {
            $status = wp_remote_retrieve_response_code( $response );
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $items = isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();

            if ( $status >= 400 ) {
                $fallback_reason = 'HTTP ' . $status;
            } elseif ( empty( $items ) ) {
                $fallback_reason = 'v4 returned 0 keychains';
            }
        }

        if ( '' !== $fallback_reason ) {
            $v3_response = self::request_with_version( 'v3', 'GET', '/v3/keychains' . $query );

            if ( ! is_wp_error( $v3_response ) ) {
                $v3_status = wp_remote_retrieve_response_code( $v3_response );
                $v3_body = json_decode( wp_remote_retrieve_body( $v3_response ), true );
                $v3_items = isset( $v3_body['data'] ) && is_array( $v3_body['data'] ) ? $v3_body['data'] : array();

                if ( $v3_status >= 200 && $v3_status < 300 && ! empty( $v3_items ) ) {
                    loft1325_log_action( 'butterflymx_list_keychains', 'Falling back to ButterflyMX v3 keychains API', array(
                        'payload' => array(
                            'params' => $params,
                            'reason' => $fallback_reason,
                        ),
                    ) );

                    $response = $v3_response;
                    $used_version = 'v3';
                }
            }
        }

        loft1325_log_action( 'butterflymx_list_keychains', 'List keychains request', array(
            'payload' => array(
                'params' => $params,
                'api_version' => $used_version,
            ),
            'response' => loft1325_redact_secrets( $response ),
        ) );

        return $response;
    }
}
