<?php
/**
 * Bypass Jetpack Protect math fallback on staging.
 *
 * Jetpack can show a math challenge fallback when its Protect API is unavailable,
 * which breaks custom login URL flows and can redirect users back to wp-login.php.
 */

if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE ) {
	add_filter(
		'jpp_allow_login',
		static function ( $allow_login ) {
			return true;
		},
		10,
		1
	);
}
