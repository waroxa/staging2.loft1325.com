<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loft1325_API_ButterflyMX {
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

        $environment = get_option( 'butterflymx_environment', 'production' );

        if ( 'v3' === $version ) {
            return ( 'sandbox' === $environment )
                ? 'https://api.na.sandbox.butterflymx.com/v3'
                : 'https://api.butterflymx.com/v3';
        }

        return ( 'sandbox' === $environment )
            ? 'https://api.na.sandbox.butterflymx.com/v4'
            : 'https://api.butterflymx.com/v4';
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

        return (string) get_option( 'butterflymx_token_' . $version, '' );
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
