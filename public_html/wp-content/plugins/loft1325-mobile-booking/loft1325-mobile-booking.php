<?php
/**
 * Plugin Name: Loft1325 Mobile Booking
 * Description: Mobile-first template overrides for ND Booking reservation and checkout pages.
 * Author: Loft1325 Automation
 * Version: 1.0.0
 * Text Domain: loft1325-mobile-booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Loft1325_Mobile_Booking' ) ) {
	final class Loft1325_Mobile_Booking {
		private static $instance = null;
		private $active_template = '';

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 99 );
			add_filter( 'template_include', array( $this, 'maybe_override_template' ), 99 );
			add_filter( 'body_class', array( $this, 'filter_body_class' ) );
		}

		public function maybe_override_template( $template ) {
			if ( ! $this->should_use_mobile_template() ) {
				return $template;
			}

			if ( is_page( 'nd-booking-page' ) ) {
				$custom = plugin_dir_path( __FILE__ ) . 'templates/mobile-booking-page.php';
				if ( file_exists( $custom ) ) {
					$this->active_template = 'page';
					return $custom;
				}
			}

			if ( is_page( 'nd-booking-checkout' ) ) {
				$custom = plugin_dir_path( __FILE__ ) . 'templates/mobile-booking-checkout.php';
				if ( file_exists( $custom ) ) {
					$this->active_template = 'checkout';
					return $custom;
				}
			}

			return $template;
		}

		public function should_use_mobile_template() {
			if ( is_admin() || is_feed() || is_embed() ) {
				return false;
			}

			if ( ! is_page( array( 'nd-booking-page', 'nd-booking-checkout' ) ) ) {
				return false;
			}

			if ( isset( $_GET['loft1325_mobile_preview'] ) && '1' === $_GET['loft1325_mobile_preview'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return true;
			}

			return wp_is_mobile();
		}

		public function enqueue_assets() {
			if ( ! $this->should_use_mobile_template() ) {
				return;
			}

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style(
				'loft1325-mobile-booking-fonts',
				'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap',
				array(),
				null
			);

			$style_path = plugin_dir_path( __FILE__ ) . 'assets/css/mobile-booking.css';
			$style_uri  = plugin_dir_url( __FILE__ ) . 'assets/css/mobile-booking.css';
			$style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';
			wp_enqueue_style( 'loft1325-mobile-booking', $style_uri, array(), $style_ver );

			$script_path = plugin_dir_path( __FILE__ ) . 'assets/js/mobile-booking.js';
			$script_uri  = plugin_dir_url( __FILE__ ) . 'assets/js/mobile-booking.js';
			$script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0';
			wp_enqueue_script( 'loft1325-mobile-booking', $script_uri, array(), $script_ver, true );
		}

		public function filter_body_class( $classes ) {
			if ( '' === $this->active_template ) {
				return $classes;
			}

			$classes[] = 'loft1325-mobile-active';
			$classes[] = 'loft1325-mobile-booking-active';
			$classes[] = 'loft1325-mobile-booking-' . $this->active_template;

			return $classes;
		}

		public function get_language() {
			$language = function_exists( 'determine_locale' ) ? (string) determine_locale() : get_locale();
			$language = strtolower( substr( $language, 0, 2 ) );
			return ( 'en' === $language ) ? 'en' : 'fr';
		}

		public function label( $fr, $en ) {
			return 'en' === $this->get_language() ? $en : $fr;
		}
	}

	Loft1325_Mobile_Booking::instance();
}
