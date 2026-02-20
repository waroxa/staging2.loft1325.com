<?php
/**
 * Plugin Name: Loft1325 Mobile Experience
 * Plugin URI: https://loft1325.com
 * Description: Provides a mobile-only homepage experience without altering the desktop layout.
 * Version: 1.0.1
 * Author: Loft1325
 * License: GPL2
 * Text Domain: loft1325-mobile
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Loft1325_Mobile_Experience' ) ) {
    final class Loft1325_Mobile_Experience {

        /**
         * Singleton instance.
         *
         * @var Loft1325_Mobile_Experience|null
         */
        private static $instance = null;

        /**
         * Tracks whether the mobile template is active for the current request.
         *
         * @var bool
         */
        private $is_mobile_template = false;

        /**
         * Get singleton instance.
         *
         * @return Loft1325_Mobile_Experience
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_mobile_assets' ) );
            add_filter( 'template_include', array( $this, 'mobile_template_include' ), 99 );
            add_filter( 'body_class', array( $this, 'filter_body_class' ) );
        }

        /**
         * Load translations.
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'loft1325-mobile', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * Enqueue mobile-only assets.
         */
        public function enqueue_mobile_assets() {
            if ( ! $this->should_apply_mobile_experience() ) {
                return;
            }

            $style_path = plugin_dir_path( __FILE__ ) . 'assets/css/mobile-style.css';
            $style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.1';
            wp_enqueue_style( 'loft1325-mobile-style', plugin_dir_url( __FILE__ ) . 'assets/css/mobile-style.css', array(), $style_ver );

            $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/mobile-script.js';
            $script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.1';
            wp_enqueue_script( 'loft1325-mobile-script', plugin_dir_url( __FILE__ ) . 'assets/js/mobile-script.js', array( 'jquery' ), $script_ver, true );
        }

        /**
         * Swap in the custom mobile template when applicable.
         *
         * @param string $template Current template path.
         *
         * @return string
         */
        public function mobile_template_include( $template ) {
            if ( ! $this->should_apply_mobile_experience() ) {
                return $template;
            }

            $mobile_template = plugin_dir_path( __FILE__ ) . 'templates/mobile-front-page.php';

            if ( ! file_exists( $mobile_template ) ) {
                return $template;
            }

            $this->is_mobile_template = true;

            return $mobile_template;
        }

        /**
         * Add a body class for easier CSS targeting.
         *
         * @param array<int,string> $classes Body classes.
         *
         * @return array<int,string>
         */
        public function filter_body_class( $classes ) {
            if ( $this->is_mobile_template ) {
                $classes[] = 'loft1325-mobile-experience-active';
            }

            return $classes;
        }

        /**
         * Determine if this request should render the mobile experience.
         *
         * @return bool
         */
        private function should_apply_mobile_experience() {
            if ( is_admin() || is_feed() || is_embed() ) {
                return false;
            }

            if ( isset( $_GET['loft1325_mobile_preview'] ) && '1' === $_GET['loft1325_mobile_preview'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return true;
            }

            if ( isset( $_GET['force_mobile'] ) && 'true' === $_GET['force_mobile'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return true;
            }

            $apply_globally = (bool) apply_filters( 'loft1325_mobile_experience_force_all_templates', false );
            if ( ! $apply_globally && ! is_front_page() ) {
                return false;
            }

            return $this->is_mobile_request();
        }

        /**
         * Detect mobile requests.
         *
         * @return bool
         */
        private function is_mobile_request() {
            if ( wp_is_mobile() ) {
                return true;
            }

            $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
            if ( '' === $user_agent ) {
                return false;
            }

            $mobile_agents = array(
                'android',
                'blackberry',
                'iphone',
                'ipad',
                'ipod',
                'opera mini',
                'windows ce',
                'windows phone',
                'palm',
                'webos',
                'symbian',
                'series60',
                'kindle',
                'mobile',
                'phone',
                'tablet',
            );

            foreach ( $mobile_agents as $agent ) {
                if ( false !== strpos( $user_agent, $agent ) ) {
                    return true;
                }
            }

            return false;
        }
    }
}

Loft1325_Mobile_Experience::instance();
