<?php
/**
 * Set a custom sender name for outgoing WordPress emails.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'wp_mail_from_name',
	static function () {
		return 'Loft1325';
	}
);
