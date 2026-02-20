<?php
/**
 * Plugin Name: Loft1325 Mobile Experience
 * Plugin URI: https://loft1325.com
 * Description: Provides a mobile-first experience for the entire Loft1325 website, extending the template-11 design.
 * Version: 1.0.0
 * Author: Manus AI
 * Author URI: https://manus.im
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Loft1325_Mobile_Experience {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_mobile_assets' ) );
        add_filter( 'template_include', array( $this, 'mobile_template_include' ), 99 ); // Synchronized priority
        add_filter( 'wp_is_mobile', array( $this, 'custom_wp_is_mobile' ) );
        add_action( 'after_setup_theme', array( $this, 'setup_mobile_theme_support' ) );
    }

    public function enqueue_mobile_assets() {
        if ( $this->should_apply_mobile_experience() ) {
            // Enqueue mobile-specific CSS
            wp_enqueue_style( 'loft1325-mobile-style', plugins_url( 'assets/css/mobile-style.css', __FILE__ ), array(), '1.0.0' );
            // Enqueue mobile-specific JavaScript
            wp_enqueue_script( 'loft1325-mobile-script', plugins_url( 'assets/js/mobile-script.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        }
    }

    public function mobile_template_include( $template ) {
        if ( $this->should_apply_mobile_experience() ) {
            // Define custom mobile template paths
            $mobile_templates = array(
                'front-page.php' => 'templates/mobile-front-page.php',
                'single.php'     => 'templates/mobile-single.php',
                'page.php'       => 'templates/mobile-page.php',
                // Add more mappings for custom post types or specific pages
            );

            foreach ( $mobile_templates as $wp_template => $mobile_template ) {
                if ( is_string( $template ) && basename( $template ) === $wp_template ) {
                    $plugin_template = plugin_dir_path( __FILE__ ) . $mobile_template;
                    if ( file_exists( $plugin_template ) ) {
                        return $plugin_template;
                    }
                }
            }

            // Fallback for other pages, try to load a generic mobile template
            $generic_mobile_template = plugin_dir_path( __FILE__ ) . 'templates/mobile-generic.php';
            if ( file_exists( $generic_mobile_template ) ) {
                return $generic_mobile_template;
            }
        }
        return $template;
    }

    public function setup_mobile_theme_support() {
        if ( $this->should_apply_mobile_experience() ) {
            // Add any mobile-specific theme support or remove desktop features
            // For example, disable certain desktop widgets or image sizes
        }
    }
}

    private function is_mobile_request() {
        // Check for a 'force_mobile' query parameter for testing
        if ( isset( $_GET['force_mobile'] ) && $_GET['force_mobile'] === 'true' ) {
            return true;
        }

        // Use WordPress's built-in mobile detection as a primary check
        if ( wp_is_mobile() ) {
            return true;
        }

        // Fallback to a more robust user-agent check if wp_is_mobile is not sufficient
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $mobile_agents = array(
            'android', 'blackberry', 'iphone', 'ipad', 'ipod', 'opera mini', 
            'windows ce', 'windows phone', 'palm', 'webos', 'symbian', 'series60', 
            'kindle', 'mobile', 'phone', 'tablet'
        );

        foreach ( $mobile_agents as $agent ) {
            if ( stripos( $user_agent, $agent ) !== false ) {
                return true;
            }
        }

        return false;
    }

    // Custom wp_is_mobile filter to ensure consistency
    public function custom_wp_is_mobile( $is_mobile ) {
        return $this->is_mobile_request();
    }

    /**
     * Determine whether the mobile experience should be applied.
     *
     * @return bool
     */
    private function should_apply_mobile_experience() {
        if ( is_admin() || is_feed() || is_embed() ) {
            return false;
        }

        // Check for a 'force_mobile' query parameter for testing
        if ( isset( $_GET['force_mobile'] ) && $_GET['force_mobile'] === 'true' ) {
            return true;
        }

        // If not front page, and not forced globally, return false for now.
        // This logic can be expanded later to include specific pages or post types.
        if ( ! is_front_page() ) {
            return false;
        }

        return $this->is_mobile_request();
    }
}

new Loft1325_Mobile_Experience();
