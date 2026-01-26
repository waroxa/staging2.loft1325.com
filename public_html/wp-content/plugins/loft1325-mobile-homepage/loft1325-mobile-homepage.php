<?php
/**
 * Plugin Name: Loft1325 Mobile Homepage
 * Description: Provides a dedicated mobile-only homepage experience without altering the desktop layout.
 * Author: Loft1325 Automation
 * Version: 1.0.0
 * Text Domain: loft1325-mobile-home
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Loft1325_Mobile_Homepage' ) ) {
    final class Loft1325_Mobile_Homepage {

        /**
         * Singleton instance.
         *
         * @var Loft1325_Mobile_Homepage|null
         */
        private static $instance = null;

        /**
         * Flag that tracks whether the mobile template is being rendered.
         *
         * @var bool
         */
        private $is_mobile_template = false;

        /**
         * Cached default strings for the mobile layout.
         *
         * @var array<string, string>
         */
        private $default_strings = array();

        /**
         * Cached language code (fr or en).
         *
         * @var string|null
         */
        private $current_language = null;

        /**
         * Tracks whether required booking dependencies are available.
         *
         * @var bool
         */
        private $dependencies_ready = false;

        /**
         * Initialize singleton instance.
         *
         * @return Loft1325_Mobile_Homepage
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Loft1325_Mobile_Homepage constructor.
         */
        private function __construct() {
            add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
            add_action( 'init', array( $this, 'evaluate_dependencies' ), 5 );
            add_action( 'init', array( $this, 'register_feature_post_type' ) );
            add_action( 'init', array( $this, 'register_image_sizes' ) );
            add_filter( 'query_vars', array( $this, 'register_preview_query_var' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
            add_filter( 'template_include', array( $this, 'maybe_use_mobile_template' ), 99 );
            add_filter( 'body_class', array( $this, 'filter_body_class' ) );
            add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
            add_action( 'template_redirect', array( $this, 'redirect_mobile_search_requests' ) );
            add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );
            add_action( 'admin_menu', array( $this, 'register_coupon_tools_page' ) );
            add_action( 'admin_post_loft1325_create_coupon', array( $this, 'handle_coupon_creation' ) );
        }

        /**
         * Validate plugin dependencies before running any front-end logic.
         */
        public function evaluate_dependencies() {
            $this->ensure_dependencies_ready();
        }

        /**
         * Load plugin text domain.
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'loft1325-mobile-home', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * Register query var that forces the mobile preview.
         *
         * @param array<string> $vars Query variables.
         *
         * @return array<string>
         */
        public function register_preview_query_var( $vars ) {
            if ( ! in_array( 'loft1325_mobile_preview', $vars, true ) ) {
                $vars[] = 'loft1325_mobile_preview';
            }

            return $vars;
        }

        /**
         * Register the custom post type that powers the feature grid.
         */
        public function register_feature_post_type() {
            $labels = array(
                'name'                  => __( 'Mobile Features', 'loft1325-mobile-home' ),
                'singular_name'         => __( 'Mobile Feature', 'loft1325-mobile-home' ),
                'add_new'               => __( 'Add New', 'loft1325-mobile-home' ),
                'add_new_item'          => __( 'Add New Feature', 'loft1325-mobile-home' ),
                'edit_item'             => __( 'Edit Feature', 'loft1325-mobile-home' ),
                'new_item'              => __( 'New Feature', 'loft1325-mobile-home' ),
                'view_item'             => __( 'View Feature', 'loft1325-mobile-home' ),
                'search_items'          => __( 'Search Features', 'loft1325-mobile-home' ),
                'not_found'             => __( 'No features found', 'loft1325-mobile-home' ),
                'not_found_in_trash'    => __( 'No features found in Trash', 'loft1325-mobile-home' ),
                'all_items'             => __( 'All Mobile Features', 'loft1325-mobile-home' ),
                'menu_name'             => __( 'Mobile Features', 'loft1325-mobile-home' ),
                'name_admin_bar'        => __( 'Mobile Feature', 'loft1325-mobile-home' ),
            );

            $args = array(
                'labels'             => $labels,
                'public'             => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_admin_bar'  => true,
                'menu_position'      => 25,
                'menu_icon'          => 'dashicons-screenoptions',
                'supports'           => array( 'title', 'thumbnail', 'editor' ),
                'exclude_from_search'=> true,
                'publicly_queryable' => false,
                'has_archive'        => false,
                'rewrite'            => false,
            );

            register_post_type( 'loft1325_mobile_feature', $args );
        }

        /**
         * Register the custom image size used throughout the layout.
         */
        public function register_image_sizes() {
            add_image_size( 'loft1325_mobile_feature_icon', 96, 96, true );
            add_image_size( 'loft1325_mobile_room_card', 720, 480, true );
        }

        /**
         * Determine whether the mobile layout should load on the current request.
         *
         * @return bool
         */
        public function should_use_mobile_layout() {
            if ( is_admin() || is_feed() || is_embed() ) {
                return false;
            }

            if ( ! $this->ensure_dependencies_ready() ) {
                return false;
            }

            $search_page_id = (int) get_option( 'nd_booking_search_page' );

            if ( $search_page_id && is_page( $search_page_id ) ) {
                return false;
            }

            if ( is_post_type_archive( 'nd_booking_cpt_1' ) ) {
                return false;
            }

            if ( isset( $_GET['loft1325_mobile_preview'] ) && '1' === $_GET['loft1325_mobile_preview'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return true;
            }

            $apply_globally = (bool) apply_filters( 'loft1325_mobile_home_force_all_templates', true );

            if ( ! $apply_globally && ! is_front_page() ) {
                return false;
            }

            $is_mobile_request = $this->is_mobile_request();

            if ( ! $is_mobile_request ) {
                return false;
            }

            return (bool) apply_filters( 'loft1325_mobile_home_force_layout', true );
        }

        /**
         * Enqueue assets required for the mobile homepage.
         */
        public function enqueue_assets() {
            if ( ! $this->should_use_mobile_layout() ) {
                return;
            }

            wp_enqueue_style( 'dashicons' );

            $style_path = plugin_dir_path( __FILE__ ) . 'assets/css/mobile-home.css';
            $style_uri  = plugin_dir_url( __FILE__ ) . 'assets/css/mobile-home.css';
            $version    = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';

            wp_enqueue_style( 'loft1325-mobile-home', $style_uri, array(), $version );
            wp_enqueue_style( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css', array(), '4.6.13' );

            $fonts_url = 'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap';
            wp_enqueue_style( 'loft1325-mobile-home-fonts', $fonts_url, array(), null );

            $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/mobile-home.js';
            $script_uri  = plugin_dir_url( __FILE__ ) . 'assets/js/mobile-home.js';
            $script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0';

            wp_enqueue_script( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js', array(), '4.6.13', true );
            wp_enqueue_script( 'flatpickr-range-plugin', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/rangePlugin.js', array( 'flatpickr' ), '4.6.13', true );

            wp_enqueue_script( 'loft1325-mobile-home', $script_uri, array( 'jquery', 'jquery-ui-datepicker', 'flatpickr', 'flatpickr-range-plugin' ), $script_ver, true );

            $this->enqueue_search_dependencies();
        }

        /**
         * Ensure the ND Booking search dependencies are available for the mobile form.
         */
        private function enqueue_search_dependencies() {
            wp_enqueue_script( 'jquery-ui-datepicker' );

            $nd_booking_search_file = WP_PLUGIN_DIR . '/nd-booking/addons/visual/search/index.php';

            if ( ! file_exists( $nd_booking_search_file ) ) {
                return;
            }

            $datepicker_path = plugin_dir_path( $nd_booking_search_file ) . 'jquery-ui-datepicker.css';

            if ( ! file_exists( $datepicker_path ) ) {
                return;
            }

            $datepicker_uri = plugin_dir_url( $nd_booking_search_file ) . 'jquery-ui-datepicker.css';
            $handle         = 'nd-booking-datepicker';
            $version        = (string) filemtime( $datepicker_path );

            if ( ! wp_style_is( $handle, 'enqueued' ) ) {
                wp_enqueue_style( $handle, $datepicker_uri, array(), $version );
            }
        }

        /**
         * Swap the front-page template with our mobile-only version when appropriate.
         *
         * @param string $template Original template path.
         *
         * @return string
         */
        public function maybe_use_mobile_template( $template ) {
            if ( ! $this->should_use_mobile_layout() ) {
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
         * Append custom body class when the mobile template is active.
         *
         * @param array<int, string> $classes Existing body classes.
         *
         * @return array<int, string>
         */
        public function filter_body_class( $classes ) {
            if ( $this->is_mobile_template ) {
                $classes[] = 'loft1325-mobile-home-active';
            }

            return $classes;
        }

        /**
         * Generate the ND Booking search form markup used on the mobile homepage.
         *
         * @return string
         */
		public function get_mobile_search_form_markup() {
			$this->enqueue_search_dependencies();

			$action = '';
			$language = $this->get_current_language();

			if ( function_exists( 'nd_booking_search_page' ) ) {
				$action = nd_booking_search_page();
			}

			if ( ! $action ) {
				$archive_link = get_post_type_archive_link( 'nd_booking_cpt_1' );
				$action       = $archive_link ? $archive_link : home_url( '/' );
			}

			if ( function_exists( 'trp_get_url_for_language' ) ) {
				$action = trp_get_url_for_language( $action, $language );
			}

			$check_in_ts     = current_time( 'timestamp' );
			$check_out_ts    = $check_in_ts + DAY_IN_SECONDS;
			$check_in_value  = '';
			$check_out_value = '';

			$default_adults   = 2;
			$default_children = 0;
			$default_total_guests = '';

            $dates_label      = $this->localize_label( 'Dates', 'Dates' );
            $date_placeholder = $this->localize_label( 'Sélectionner les dates', 'Select dates' );
            $adults_label     = $this->localize_label( 'Adultes', 'Adults' );
            $children_label   = $this->localize_label( 'Enfants (0–18 ans)', 'Children (0–18 yrs)' );
            $language_attr    = ( 'en' === $language ) ? 'en' : 'fr';

            ob_start();
            ?>
            <form id="nd_booking_search_cpt_1_form_sidebar" class="loft-search-toolbar__form loft-search-toolbar__form--card" action="<?php echo esc_url( $action ); ?>" method="get" data-language="<?php echo esc_attr( $language_attr ); ?>">
                <div id="nd_booking_search_main_bg" class="loft-search-toolbar nd_booking_search_form">
                    <div class="loft-booking-card">
                        <div class="loft-search-toolbar__field loft-search-toolbar__field--date-range" data-date-field>
                            <label class="loft-search-toolbar__label" for="loft_booking_date_range"><?php echo esc_html( $dates_label ); ?></label>
                            <div class="loft-booking-card__date-input">
                                <input
                                    type="text"
                                    id="loft_booking_date_range"
                                    class="loft-booking-card__input loft-booking-card__input--date"
                                    placeholder="<?php echo esc_attr( $date_placeholder ); ?>"
                                    autocomplete="off"
                                    readonly
                                    aria-label="<?php echo esc_attr( $dates_label ); ?>"
                                />
                                <button type="button" class="loft-booking-card__clear" aria-label="<?php echo esc_attr( $this->localize_label( 'Effacer la plage de dates', 'Clear date range' ) ); ?>" data-date-clear>&times;</button>
                                <span class="loft-booking-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                        <rect x="4" y="5" width="16" height="16" rx="2"></rect>
                                        <line x1="16" y1="3" x2="16" y2="7"></line>
                                        <line x1="8" y1="3" x2="8" y2="7"></line>
                                        <line x1="4" y1="11" x2="20" y2="11"></line>
                                    </svg>
                                </span>
                            </div>
                            <input type="text" id="nd_booking_archive_form_date_range_from" name="nd_booking_archive_form_date_range_from" class="loft-booking-card__hidden-input loft-search-toolbar__input" value="<?php echo esc_attr( $check_in_value ); ?>" autocomplete="off" readonly />
                            <input type="text" id="nd_booking_archive_form_date_range_to" name="nd_booking_archive_form_date_range_to" class="loft-booking-card__hidden-input loft-search-toolbar__input" value="<?php echo esc_attr( $check_out_value ); ?>" autocomplete="off" readonly />
                        </div>

                        <div class="loft-booking-card__grid">
                            <div class="loft-search-toolbar__field loft-search-toolbar__field--guests">
                                <label class="loft-search-toolbar__label" for="loft_booking_adults"><?php echo esc_html( $adults_label ); ?></label>
                                <div class="loft-search-toolbar__control loft-search-toolbar__control--guests loft-search-toolbar__group loft-search-toolbar__guests" data-guest-group="adults">
                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="down" aria-label="<?php echo esc_attr( $this->localize_label( 'Diminuer le nombre d’adultes', 'Decrease adult count' ) ); ?>">−</button>
                                    <span class="loft-search-toolbar__guests-value" id="loft_booking_adults_value"><?php echo esc_html( $default_adults ); ?></span>
                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="up" aria-label="<?php echo esc_attr( $this->localize_label( 'Augmenter le nombre d’adultes', 'Increase adult count' ) ); ?>">+</button>
                                    <input type="hidden" id="loft_booking_adults" value="<?php echo esc_attr( $default_adults ); ?>" />
                                </div>
                            </div>

                            <div class="loft-search-toolbar__field loft-search-toolbar__field--guests">
                                <label class="loft-search-toolbar__label" for="loft_booking_children"><?php echo esc_html( $children_label ); ?></label>
                                <div class="loft-search-toolbar__control loft-search-toolbar__control--guests loft-search-toolbar__group loft-search-toolbar__guests" data-guest-group="children">
                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="down" aria-label="<?php echo esc_attr( $this->localize_label( 'Diminuer le nombre d’enfants', 'Decrease child count' ) ); ?>">−</button>
                                    <span class="loft-search-toolbar__guests-value" id="loft_booking_children_value"><?php echo esc_html( $default_children ); ?></span>
                                    <button type="button" class="loft-search-toolbar__guest-btn" data-direction="up" aria-label="<?php echo esc_attr( $this->localize_label( 'Augmenter le nombre d’enfants', 'Increase child count' ) ); ?>">+</button>
                                    <input type="hidden" id="loft_booking_children" value="<?php echo esc_attr( $default_children ); ?>" />
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="nd_booking_archive_form_guests" name="nd_booking_archive_form_guests" value="<?php echo esc_attr( $default_total_guests ); ?>" />

                        <div class="loft-search-toolbar__field loft-search-toolbar__field--actions">
                            <button type="submit" class="loft-search-card__btn loft-search-card__btn--primary loft-search-toolbar__submit"><?php echo esc_html( $this->get_string( 'search_submit_label' ) ); ?></button>
                        </div>
                    </div>
                </div>

            </form>
            <?php

            return trim( ob_get_clean() );
        }

        /**
         * When the mobile homepage handles ND Booking search requests, forward them to the
         * standard search results endpoint so visitors see the same results as desktop.
         */
        public function redirect_mobile_search_requests() {
            if ( is_admin() ) {
                return;
            }

            if ( ! $this->should_use_mobile_layout() ) {
                return;
            }

            $target = '';
            $search_page_id = 0;

			if ( function_exists( 'nd_booking_search_page' ) ) {
				$target = nd_booking_search_page();
				$search_page_id = (int) get_option( 'nd_booking_search_page' );
			}

			if ( ! $target ) {
				$target = get_post_type_archive_link( 'nd_booking_cpt_1' );
			}

			if ( ! $target ) {
				return;
			}

            if ( $search_page_id && is_page( $search_page_id ) ) {
                return;
            }

            if ( is_post_type_archive( 'nd_booking_cpt_1' ) ) {
                return;
            }

			$language = $this->get_current_language();

			if ( function_exists( 'trp_get_url_for_language' ) ) {
				$target = trp_get_url_for_language( $target, $language );
			}

            $query_args = isset( $_GET ) ? (array) wp_unslash( $_GET ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            unset( $query_args['post_type'] );

            $has_search_params = isset( $query_args['nd_booking_archive_form_date_range_from'] ) || isset( $query_args['nd_booking_archive_form_date_range_to'] ) || isset( $query_args['nd_booking_archive_form_guests'] );

            if ( ! $has_search_params ) {
                return;
            }

            $query_args = array_filter(
                $query_args,
                static function ( $value, $key ) {
                    if ( 'nd_booking_archive_form_guests' === $key ) {
                        return true;
                    }

                    return '' !== $value && null !== $value;
                },
                ARRAY_FILTER_USE_BOTH
            );

            $query_string = http_build_query( $query_args );

            $redirect_url = $target;

            if ( $query_string ) {
                $redirect_url .= ( false === strpos( $target, '?' ) ? '?' : '&' ) . $query_string;
            }

            wp_safe_redirect( $redirect_url );
            exit;
        }

        /**
         * Retrieve the active language.
         *
         * @return string fr or en.
         */
        public function get_current_language() {
            if ( null !== $this->current_language ) {
                return $this->current_language;
            }

            $language = 'fr';

            if ( function_exists( 'trp_get_current_language' ) ) {
                $language = (string) trp_get_current_language();
            } else {
                $language = function_exists( 'determine_locale' ) ? (string) determine_locale() : get_locale();
            }

            $language          = strtolower( substr( $language, 0, 2 ) );
            $this->current_language = ( 'en' === $language ) ? 'en' : 'fr';

            return $this->current_language;
        }

        /**
         * Retrieve default strings used in the layout.
         *
         * @return array<string, string>
         */
        public function get_default_strings() {
            $language = $this->get_current_language();

            if ( isset( $this->default_strings[ $language ] ) ) {
                return $this->default_strings[ $language ];
            }

            $this->default_strings['fr'] = array(
                'hero_tagline'           => __( 'Concierge Virtuel', 'loft1325-mobile-home' ),
                'hero_title'             => __( 'Expérience Hôtelière 100% Virtuelle', 'loft1325-mobile-home' ),
                'hero_description'       => __( "Pour le prix d'une chambre d'hôtel, offrez-vous tout le confort d'une maison et une expérience entièrement autonome. Notre concept unique vous permet de gérer votre séjour directement depuis votre mobile, sans réception ni attente. Créez vos propres clés numériques, invitez vos proches et contrôlez vos réservations en quelques clics seulement.", 'loft1325-mobile-home' ),
                'hero_primary_label'     => __( 'Réserver', 'loft1325-mobile-home' ),
                'hero_primary_url'       => '#loft1325-mobile-home-search',
                'hero_secondary_label'   => __( 'Nous contacter', 'loft1325-mobile-home' ),
                'hero_secondary_url'     => '/contact',
                'search_card_title'      => __( 'Concierge Virtuel', 'loft1325-mobile-home' ),
                'search_location_label'  => __( 'Où', 'loft1325-mobile-home' ),
                'search_location_value'  => '',
                'search_date_label'      => __( 'Quand', 'loft1325-mobile-home' ),
                'search_guests_label'    => __( 'Invités', 'loft1325-mobile-home' ),
                'search_submit_label'    => __( 'Rechercher', 'loft1325-mobile-home' ),
                'rooms_heading'          => __( 'Lofts Haut de Gamme', 'loft1325-mobile-home' ),
                'rooms_description'      => __( "Contrairement aux chambres d'hôtel traditionnelles, nos lofts offrent un espace de vie plus généreux, souvent 1,5 à 3 fois plus grand au même prix qu'une chambre d'hôtel.", 'loft1325-mobile-home' ),
                'rooms_button_label'     => __( 'Réserver', 'loft1325-mobile-home' ),
                'rooms_view_all_label'   => __( 'Voir tous les lofts', 'loft1325-mobile-home' ),
                'cta_heading'            => __( "Prêt à vivre l'expérience?", 'loft1325-mobile-home' ),
                'cta_description'        => __( 'Réservez dès maintenant votre séjour et découvrez une nouvelle façon de voyager.', 'loft1325-mobile-home' ),
                'cta_primary_label'      => __( 'Réserver', 'loft1325-mobile-home' ),
                'cta_primary_url'        => '#loft1325-mobile-home-search',
                'cta_secondary_label'    => __( 'Nous contacter', 'loft1325-mobile-home' ),
                'cta_secondary_url'      => '/contact',
                'footer_nav_heading'     => __( 'Navigation', 'loft1325-mobile-home' ),
                'footer_support_heading' => __( 'Support', 'loft1325-mobile-home' ),
                'footer_social_heading'  => __( 'Suivez-nous', 'loft1325-mobile-home' ),
                'footer_legal'           => __( 'Expérience hôtelière 100% virtuelle', 'loft1325-mobile-home' ),
                'footer_copyright'       => sprintf( __( '© %1$s Loft1325. Tous droits réservés. | CITQ Certificat: 301842', 'loft1325-mobile-home' ), date_i18n( 'Y' ) ),
            );

            $this->default_strings['en'] = array(
                'hero_tagline'           => __( 'Virtual Concierge', 'loft1325-mobile-home' ),
                'hero_title'             => __( '100% Virtual Hotel Experience', 'loft1325-mobile-home' ),
                'hero_description'       => __( "For the price of a hotel room, enjoy the comfort of a home and a fully self-service stay. Our unique concept lets you manage your visit from your phone with no front desk or waiting. Create your own digital keys, invite guests, and control bookings in just a few taps.", 'loft1325-mobile-home' ),
                'hero_primary_label'     => __( 'Book Now', 'loft1325-mobile-home' ),
                'hero_primary_url'       => '#loft1325-mobile-home-search',
                'hero_secondary_label'   => __( 'Contact Us', 'loft1325-mobile-home' ),
                'hero_secondary_url'     => '/contact',
                'search_card_title'      => __( 'Virtual Concierge', 'loft1325-mobile-home' ),
                'search_location_label'  => __( 'Where', 'loft1325-mobile-home' ),
                'search_location_value'  => '',
                'search_date_label'      => __( 'When', 'loft1325-mobile-home' ),
                'search_guests_label'    => __( 'Guests', 'loft1325-mobile-home' ),
                'search_submit_label'    => __( 'Search', 'loft1325-mobile-home' ),
                'rooms_heading'          => __( 'Premium Lofts', 'loft1325-mobile-home' ),
                'rooms_description'      => __( 'Unlike traditional hotel rooms, our lofts offer a more generous living space—often 1.5 to 3 times larger for the same price as a hotel room.', 'loft1325-mobile-home' ),
                'rooms_button_label'     => __( 'Book Now', 'loft1325-mobile-home' ),
                'rooms_view_all_label'   => __( 'See all lofts', 'loft1325-mobile-home' ),
                'cta_heading'            => __( 'Ready to experience it?', 'loft1325-mobile-home' ),
                'cta_description'        => __( 'Book your stay now and discover a new way to travel.', 'loft1325-mobile-home' ),
                'cta_primary_label'      => __( 'Book Now', 'loft1325-mobile-home' ),
                'cta_primary_url'        => '#loft1325-mobile-home-search',
                'cta_secondary_label'    => __( 'Contact Us', 'loft1325-mobile-home' ),
                'cta_secondary_url'      => '/contact',
                'footer_nav_heading'     => __( 'Navigation', 'loft1325-mobile-home' ),
                'footer_support_heading' => __( 'Support', 'loft1325-mobile-home' ),
                'footer_social_heading'  => __( 'Follow us', 'loft1325-mobile-home' ),
                'footer_legal'           => __( '100% virtual hotel experience', 'loft1325-mobile-home' ),
                'footer_copyright'       => sprintf( __( '© %1$s Loft1325. All rights reserved. | CITQ Certificate: 301842', 'loft1325-mobile-home' ), date_i18n( 'Y' ) ),
            );

            return $this->default_strings[ $language ];
        }

        /**
         * Fetch a localized string, falling back to defaults as needed.
         *
         * @param string $key String identifier.
         *
         * @return string
         */
        public function get_string( $key ) {
            $defaults  = $this->get_default_strings();
            $language  = $this->get_current_language();
            $setting   = get_theme_mod( 'loft1325_mobile_home_' . $key );
            $has_setting = is_string( $setting ) && '' !== trim( $setting );

            if ( 'fr' === $language && $has_setting ) {
                return $setting;
            }

            return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
        }

        /**
         * Quickly return a localized string for inline labels.
         *
         * @param string $french  French text.
         * @param string $english English text.
         *
         * @return string
         */
        public function localize_label( $french, $english ) {
            return ( 'en' === $this->get_current_language() ) ? $english : $french;
        }

        /**
         * Retrieve social links configured in the Customizer.
         *
         * @return array<int, array<string, string>>
         */
        public function get_social_links() {
            $links = array(
                array(
                    'label' => 'Airbnb',
                    'url'   => get_theme_mod( 'loft1325_mobile_home_social_airbnb', '' ),
                ),
                array(
                    'label' => 'Trip Advisor',
                    'url'   => get_theme_mod( 'loft1325_mobile_home_social_tripadvisor', '' ),
                ),
                array(
                    'label' => 'Instagram',
                    'url'   => get_theme_mod( 'loft1325_mobile_home_social_instagram', '' ),
                ),
            );

            return array_filter(
                $links,
                static function( $link ) {
                    return ! empty( $link['url'] );
                }
            );
        }

        /**
         * Retrieve feature cards, either from the custom post type or fallback defaults.
         *
         * @return array<int, array<string, string>>
         */
        public function get_feature_cards() {
            $features = array();

            $query = new WP_Query(
                array(
                    'post_type'      => 'loft1325_mobile_feature',
                    'post_status'    => 'publish',
                    'posts_per_page' => 8,
                    'orderby'        => array(
                        'menu_order' => 'ASC',
                        'date'       => 'DESC',
                    ),
                )
            );

            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();

                    $image = get_the_post_thumbnail_url( get_the_ID(), 'loft1325_mobile_feature_icon' );

                    $features[] = array(
                        'title'       => get_the_title(),
                        'description' => has_excerpt() ? get_the_excerpt() : '',
                        'image'       => $image,
                        'icon'        => '',
                    );
                }

                wp_reset_postdata();

                return $features;
            }

            $fallbacks = array(
                array(
                    'title' => __( 'Gestion mobile', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-smartphone',
                ),
                array(
                    'title' => __( 'Check-in 24/7', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-clock',
                ),
                array(
                    'title' => __( '100% Sécurisé', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-lock',
                ),
                array(
                    'title' => __( 'Certifié CITQ', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-awards',
                ),
                array(
                    'title' => __( 'Choisir chambre et date', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-calendar-alt',
                ),
                array(
                    'title' => __( 'Paiement en ligne', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-cart',
                ),
                array(
                    'title' => __( 'Confirmation paiement', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-yes',
                ),
                array(
                    'title' => __( 'Recevez clé virtuelle', 'loft1325-mobile-home' ),
                    'icon'  => 'dashicons-unlock',
                ),
            );

            return $fallbacks;
        }

        /**
         * Gather room cards populated from ND Booking rooms.
         *
         * @return array<int, array<string, string>>
         */
        public function get_room_cards() {
            $rooms = array();

            $query = new WP_Query(
                array(
                    'post_type'      => 'nd_booking_cpt_1',
                    'post_status'    => 'publish',
                    'posts_per_page' => 3,
                    'meta_query'     => array(
                        array(
                            'key'     => '_thumbnail_id',
                            'compare' => 'EXISTS',
                        ),
                    ),
                )
            );

            if ( ! $query->have_posts() ) {
                return $rooms;
            }

            while ( $query->have_posts() ) {
                $query->the_post();

                $price      = '';
                $currency   = '';
                $room_id    = get_the_ID();
                $rating_raw = get_post_meta( $room_id, 'loft1325_room_rating', true );

                if ( empty( $rating_raw ) ) {
                    $rating_raw = get_post_meta( $room_id, 'nd_booking_meta_box_review_average', true );
                }

                if ( empty( $rating_raw ) ) {
                    $rating_raw = get_post_meta( $room_id, 'nd_booking_meta_box_stars', true );
                }

                if ( '' !== $rating_raw && is_numeric( $rating_raw ) ) {
                    $rating_raw = number_format_i18n( (float) $rating_raw, 1 );
                }

                if ( function_exists( 'nd_booking_get_final_price' ) ) {
                    $price = nd_booking_get_final_price( $room_id, current_time( 'm/d/Y' ) );
                }

                if ( function_exists( 'nd_booking_get_currency' ) ) {
                    $currency = nd_booking_get_currency();
                }

                if ( ! is_numeric( $price ) ) {
                    $price = '';
                }

                $rooms[] = array(
                    'title'       => get_the_title(),
                    'permalink'   => get_permalink(),
                    'excerpt'     => has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : wp_trim_words( wp_strip_all_tags( get_the_content() ), 24 ),
                    'image'       => get_the_post_thumbnail_url( $room_id, 'loft1325_mobile_room_card' ),
                    'price'       => $price,
                    'currency'    => $currency,
                    'rating'      => $rating_raw,
                );
            }

            wp_reset_postdata();

            return $rooms;
        }

        /**
         * Register Customizer controls that allow quick content tweaks.
         *
         * @param WP_Customize_Manager $wp_customize Customizer instance.
         */
        public function register_customizer_settings( $wp_customize ) {
            if ( ! ( $wp_customize instanceof WP_Customize_Manager ) ) {
                return;
            }

            $wp_customize->add_section(
                'loft1325_mobile_home',
                array(
                    'title'      => __( 'Mobile Homepage', 'loft1325-mobile-home' ),
                    'priority'   => 35,
                    'capability' => 'edit_theme_options',
                )
            );

            $fields = array(
                'hero_tagline'         => array( 'type' => 'text' ),
                'hero_title'           => array( 'type' => 'text' ),
                'hero_description'     => array( 'type' => 'textarea' ),
                'hero_primary_label'   => array( 'type' => 'text' ),
                'hero_primary_url'     => array( 'type' => 'url' ),
                'hero_secondary_label' => array( 'type' => 'text' ),
                'hero_secondary_url'   => array( 'type' => 'url' ),
                'rooms_heading'        => array( 'type' => 'text' ),
                'rooms_description'    => array( 'type' => 'textarea' ),
                'rooms_button_label'   => array( 'type' => 'text' ),
                'rooms_view_all_label' => array( 'type' => 'text' ),
                'cta_heading'          => array( 'type' => 'text' ),
                'cta_description'      => array( 'type' => 'textarea' ),
                'cta_primary_label'    => array( 'type' => 'text' ),
                'cta_primary_url'      => array( 'type' => 'url' ),
                'cta_secondary_label'  => array( 'type' => 'text' ),
                'cta_secondary_url'    => array( 'type' => 'url' ),
                'footer_nav_heading'   => array( 'type' => 'text' ),
                'footer_support_heading' => array( 'type' => 'text' ),
                'footer_social_heading'  => array( 'type' => 'text' ),
                'footer_legal'           => array( 'type' => 'textarea' ),
                'footer_copyright'       => array( 'type' => 'textarea' ),
            );

            foreach ( $fields as $key => $config ) {
                $default = $this->get_string( $key );

                $sanitize_callback = 'sanitize_text_field';
                if ( 'textarea' === $config['type'] ) {
                    $sanitize_callback = 'sanitize_textarea_field';
                } elseif ( 'url' === $config['type'] ) {
                    $sanitize_callback = 'esc_url_raw';
                }

                $wp_customize->add_setting(
                    'loft1325_mobile_home_' . $key,
                    array(
                        'default'           => $default,
                        'sanitize_callback' => $sanitize_callback,
                        'transport'         => 'refresh',
                    )
                );

                $control_args = array(
                    'label'    => ucfirst( str_replace( '_', ' ', $key ) ),
                    'section'  => 'loft1325_mobile_home',
                    'settings' => 'loft1325_mobile_home_' . $key,
                );

                if ( 'textarea' === $config['type'] ) {
                    $control_args['type'] = 'textarea';
                } elseif ( 'url' === $config['type'] ) {
                    $control_args['type'] = 'url';
                } else {
                    $control_args['type'] = 'text';
                }

                $wp_customize->add_control( 'loft1325_mobile_home_' . $key, $control_args );
            }

            $wp_customize->add_setting(
                'loft1325_mobile_home_social_airbnb',
                array(
                    'default'           => '',
                    'sanitize_callback' => 'esc_url_raw',
                )
            );
            $wp_customize->add_control(
                'loft1325_mobile_home_social_airbnb',
                array(
                    'label'    => __( 'Airbnb URL', 'loft1325-mobile-home' ),
                    'section'  => 'loft1325_mobile_home',
                    'type'     => 'url',
                )
            );

            $wp_customize->add_setting(
                'loft1325_mobile_home_social_tripadvisor',
                array(
                    'default'           => '',
                    'sanitize_callback' => 'esc_url_raw',
                )
            );
            $wp_customize->add_control(
                'loft1325_mobile_home_social_tripadvisor',
                array(
                    'label'    => __( 'TripAdvisor URL', 'loft1325-mobile-home' ),
                    'section'  => 'loft1325_mobile_home',
                    'type'     => 'url',
                )
            );

            $wp_customize->add_setting(
                'loft1325_mobile_home_social_instagram',
                array(
                    'default'           => '',
                    'sanitize_callback' => 'esc_url_raw',
                )
            );
            $wp_customize->add_control(
                'loft1325_mobile_home_social_instagram',
                array(
                    'label'    => __( 'Instagram URL', 'loft1325-mobile-home' ),
                    'section'  => 'loft1325_mobile_home',
                    'type'     => 'url',
                )
            );

            $wp_customize->add_setting(
                'loft1325_mobile_home_hero_background',
                array(
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                )
            );

            $wp_customize->add_control(
                new WP_Customize_Media_Control(
                    $wp_customize,
                    'loft1325_mobile_home_hero_background',
                    array(
                        'label'    => __( 'Hero Background Image', 'loft1325-mobile-home' ),
                        'section'  => 'loft1325_mobile_home',
                        'mime_type'=> 'image',
                    )
                )
            );
        }

        /**
         * Register the discount code helper under the Hotel Booking menu.
         */
        public function register_coupon_tools_page() {
            if ( ! function_exists( 'MPHB' ) || ! class_exists( '\MPHB\PostTypes\CouponCPT' ) ) {
                return;
            }

            add_submenu_page(
                'index.php',
                __( 'Discount Codes', 'loft1325-mobile-home' ),
                __( 'Discount Codes', 'loft1325-mobile-home' ),
                'edit_mphb_coupons',
                'loft1325-discount-codes',
                array( $this, 'render_coupon_tools_page' )
            );
        }

        /**
         * Render the discount code helper page.
         */
        public function render_coupon_tools_page() {
            if ( ! current_user_can( 'edit_mphb_coupons' ) ) {
                return;
            }

            if ( ! function_exists( 'MPHB' ) || ! class_exists( '\MPHB\PostTypes\CouponCPT' ) ) {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'Hotel Booking coupons are unavailable because the MotoPress Hotel Booking plugin is not active.', 'loft1325-mobile-home' ) . '</p></div>';
                return;
            }

            $created_id = isset( $_GET['loft1325_coupon_created'] ) ? absint( $_GET['loft1325_coupon_created'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $error_code = isset( $_GET['loft1325_coupon_error'] ) ? sanitize_key( $_GET['loft1325_coupon_error'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            if ( $created_id ) {
                $edit_url = esc_url( admin_url( 'post.php?post=' . $created_id . '&action=edit' ) );
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Discount code created.', 'loft1325-mobile-home' ) . ' <a href="' . $edit_url . '">' . esc_html__( 'Edit coupon', 'loft1325-mobile-home' ) . '</a></p></div>';
            } elseif ( $error_code ) {
                $message = esc_html__( 'Please review the form and try again.', 'loft1325-mobile-home' );

                if ( 'missing_code' === $error_code ) {
                    $message = esc_html__( 'A coupon code is required.', 'loft1325-mobile-home' );
                } elseif ( 'missing_amount' === $error_code ) {
                    $message = esc_html__( 'Please provide a discount amount for this preset.', 'loft1325-mobile-home' );
                } elseif ( 'invalid_preset' === $error_code ) {
                    $message = esc_html__( 'Please select a valid discount preset.', 'loft1325-mobile-home' );
                } elseif ( 'duplicate_code' === $error_code ) {
                    $message = esc_html__( 'That coupon code already exists.', 'loft1325-mobile-home' );
                } elseif ( 'insert_failed' === $error_code ) {
                    $message = esc_html__( 'We could not create the coupon. Please try again.', 'loft1325-mobile-home' );
                }

                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            }

            $presets = $this->get_coupon_presets();
            $coupons_enabled = MPHB()->settings()->main()->isCouponsEnabled();
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Discount Codes', 'loft1325-mobile-home' ); ?></h1>
                <p><?php esc_html_e( 'Create popular hotel discount codes that work with the MotoPress Hotel Booking checkout.', 'loft1325-mobile-home' ); ?></p>
                <?php if ( ! $coupons_enabled ) : ?>
                    <div class="notice notice-warning inline"><p><?php esc_html_e( 'Coupons are currently disabled in Hotel Booking settings. Enable them to allow guests to redeem these codes.', 'loft1325-mobile-home' ); ?></p></div>
                <?php endif; ?>
                <p>
                    <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mphb_coupon' ) ); ?>">
                        <?php esc_html_e( 'View all coupons', 'loft1325-mobile-home' ); ?>
                    </a>
                </p>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'loft1325_create_coupon' ); ?>
                    <input type="hidden" name="action" value="loft1325_create_coupon" />

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_code"><?php esc_html_e( 'Coupon Code', 'loft1325-mobile-home' ); ?></label></th>
                                <td><input type="text" class="regular-text" id="loft1325_coupon_code" name="loft1325_coupon_code" required /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_preset"><?php esc_html_e( 'Discount Preset', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <select id="loft1325_coupon_preset" name="loft1325_coupon_preset">
                                        <?php foreach ( $presets as $preset_key => $preset ) : ?>
                                            <option value="<?php echo esc_attr( $preset_key ); ?>"><?php echo esc_html( $preset['label'] ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Pick a common hotel discount type like early-bird, last-minute, or fixed amount off.', 'loft1325-mobile-home' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_amount"><?php esc_html_e( 'Discount Amount', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <input type="number" step="0.01" min="0" id="loft1325_coupon_amount" name="loft1325_coupon_amount" class="small-text" />
                                    <p class="description"><?php esc_html_e( 'Required for percentage/fixed discounts. Ignored for the 100% off preset.', 'loft1325-mobile-home' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_min_days"><?php esc_html_e( 'Min Days Before Check-in', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <input type="number" step="1" min="0" id="loft1325_coupon_min_days" name="loft1325_coupon_min_days" class="small-text" />
                                    <p class="description"><?php esc_html_e( 'Used for early-bird discounts.', 'loft1325-mobile-home' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_max_days"><?php esc_html_e( 'Max Days Before Check-in', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <input type="number" step="1" min="0" id="loft1325_coupon_max_days" name="loft1325_coupon_max_days" class="small-text" />
                                    <p class="description"><?php esc_html_e( 'Used for last-minute discounts.', 'loft1325-mobile-home' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_min_nights"><?php esc_html_e( 'Minimum Nights', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <input type="number" step="1" min="1" id="loft1325_coupon_min_nights" name="loft1325_coupon_min_nights" class="small-text" />
                                    <p class="description"><?php esc_html_e( 'Used for long-stay discounts.', 'loft1325-mobile-home' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_max_nights"><?php esc_html_e( 'Maximum Nights', 'loft1325-mobile-home' ); ?></label></th>
                                <td>
                                    <input type="number" step="1" min="0" id="loft1325_coupon_max_nights" name="loft1325_coupon_max_nights" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_expiration"><?php esc_html_e( 'Expiration Date', 'loft1325-mobile-home' ); ?></label></th>
                                <td><input type="date" id="loft1325_coupon_expiration" name="loft1325_coupon_expiration" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_usage_limit"><?php esc_html_e( 'Usage Limit', 'loft1325-mobile-home' ); ?></label></th>
                                <td><input type="number" step="1" min="0" id="loft1325_coupon_usage_limit" name="loft1325_coupon_usage_limit" class="small-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="loft1325_coupon_description"><?php esc_html_e( 'Internal Description', 'loft1325-mobile-home' ); ?></label></th>
                                <td><textarea id="loft1325_coupon_description" name="loft1325_coupon_description" class="large-text" rows="3"></textarea></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php submit_button( __( 'Create discount code', 'loft1325-mobile-home' ) ); ?>
                </form>
            </div>
            <?php
        }

        /**
         * Handle creation of new coupon presets.
         */
        public function handle_coupon_creation() {
            if ( ! current_user_can( 'edit_mphb_coupons' ) ) {
                wp_die( esc_html__( 'You are not allowed to create coupons.', 'loft1325-mobile-home' ) );
            }

            check_admin_referer( 'loft1325_create_coupon' );

            if ( ! function_exists( 'MPHB' ) || ! class_exists( '\MPHB\PostTypes\CouponCPT' ) ) {
                wp_die( esc_html__( 'Hotel Booking coupons are unavailable.', 'loft1325-mobile-home' ) );
            }

            $code        = isset( $_POST['loft1325_coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_coupon_code'] ) ) : '';
            $preset_key  = isset( $_POST['loft1325_coupon_preset'] ) ? sanitize_key( wp_unslash( $_POST['loft1325_coupon_preset'] ) ) : '';
            $amount      = isset( $_POST['loft1325_coupon_amount'] ) ? (float) wp_unslash( $_POST['loft1325_coupon_amount'] ) : 0;
            $min_days    = isset( $_POST['loft1325_coupon_min_days'] ) ? absint( $_POST['loft1325_coupon_min_days'] ) : 0;
            $max_days    = isset( $_POST['loft1325_coupon_max_days'] ) ? absint( $_POST['loft1325_coupon_max_days'] ) : 0;
            $min_nights  = isset( $_POST['loft1325_coupon_min_nights'] ) ? absint( $_POST['loft1325_coupon_min_nights'] ) : 0;
            $max_nights  = isset( $_POST['loft1325_coupon_max_nights'] ) ? absint( $_POST['loft1325_coupon_max_nights'] ) : 0;
            $expiration  = isset( $_POST['loft1325_coupon_expiration'] ) ? sanitize_text_field( wp_unslash( $_POST['loft1325_coupon_expiration'] ) ) : '';
            $usage_limit = isset( $_POST['loft1325_coupon_usage_limit'] ) ? absint( $_POST['loft1325_coupon_usage_limit'] ) : 0;
            $description = isset( $_POST['loft1325_coupon_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['loft1325_coupon_description'] ) ) : '';

            if ( '' === $code ) {
                $this->redirect_coupon_error( 'missing_code' );
            }

            $presets = $this->get_coupon_presets();

            if ( ! isset( $presets[ $preset_key ] ) ) {
                $this->redirect_coupon_error( 'invalid_preset' );
            }

            if ( $presets[ $preset_key ]['requires_amount'] && $amount <= 0 ) {
                $this->redirect_coupon_error( 'missing_amount' );
            }

            $existing = get_page_by_title( $code, OBJECT, 'mphb_coupon' );

            if ( $existing ) {
                $this->redirect_coupon_error( 'duplicate_code' );
            }

            $coupon_id = wp_insert_post(
                array(
                    'post_title'  => $code,
                    'post_status' => 'publish',
                    'post_type'   => 'mphb_coupon',
                ),
                true
            );

            if ( is_wp_error( $coupon_id ) ) {
                $this->redirect_coupon_error( 'insert_failed' );
            }

            $preset = $presets[ $preset_key ];

            $room_amount    = $preset['room_amount'];
            $service_amount = $preset['service_amount'];
            $fee_amount     = $preset['fee_amount'];

            if ( 'amount' === $room_amount ) {
                $room_amount = $amount;
            }

            if ( 'amount' === $service_amount ) {
                $service_amount = $amount;
            }

            if ( 'amount' === $fee_amount ) {
                $fee_amount = $amount;
            }

            update_post_meta( $coupon_id, '_mphb_description', $description );
            update_post_meta( $coupon_id, '_mphb_room_discount_type', $preset['room_discount_type'] );
            update_post_meta( $coupon_id, '_mphb_service_discount_type', $preset['service_discount_type'] );
            update_post_meta( $coupon_id, '_mphb_fee_discount_type', $preset['fee_discount_type'] );
            update_post_meta( $coupon_id, '_mphb_room_amount', $room_amount );
            update_post_meta( $coupon_id, '_mphb_service_amount', $service_amount );
            update_post_meta( $coupon_id, '_mphb_fee_amount', $fee_amount );
            update_post_meta( $coupon_id, '_mphb_include_room_types', array() );
            update_post_meta( $coupon_id, '_mphb_include_services', array() );
            update_post_meta( $coupon_id, '_mphb_include_fees', array() );
            update_post_meta( $coupon_id, '_mphb_min_days_before_check_in', $preset['min_days_before_check_in'] ? $min_days : 0 );
            update_post_meta( $coupon_id, '_mphb_max_days_before_check_in', $preset['max_days_before_check_in'] ? $max_days : 0 );
            update_post_meta( $coupon_id, '_mphb_min_nights', $preset['min_nights'] ? max( 1, $min_nights ) : 1 );
            update_post_meta( $coupon_id, '_mphb_max_nights', $preset['max_nights'] ? $max_nights : 0 );
            update_post_meta( $coupon_id, '_mphb_usage_limit', $usage_limit );
            update_post_meta( $coupon_id, '_mphb_usage_count', 0 );
            update_post_meta( $coupon_id, '_mphb_expiration_date', $expiration );

            $redirect_url = add_query_arg(
                array(
                    'page'                      => 'loft1325-discount-codes',
                    'loft1325_coupon_created'   => $coupon_id,
                ),
                admin_url( 'admin.php' )
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }

        /**
         * Redirect back to the discount tool with an error.
         *
         * @param string $code Error code.
         */
        private function redirect_coupon_error( $code ) {
            $redirect_url = add_query_arg(
                array(
                    'page'                   => 'loft1325-discount-codes',
                    'loft1325_coupon_error'  => $code,
                ),
                admin_url( 'admin.php' )
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }

        /**
         * Define preset configurations for coupon creation.
         *
         * @return array<string, array<string, mixed>>
         */
        private function get_coupon_presets() {
            if ( ! class_exists( '\MPHB\PostTypes\CouponCPT' ) ) {
                return array();
            }

            $coupon = '\MPHB\PostTypes\CouponCPT';

            return array(
                'percentage' => array(
                    'label'                  => __( 'Percentage off accommodation', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_PERCENTAGE,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_NONE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_NONE,
                    'room_amount'            => 'amount',
                    'service_amount'         => 0,
                    'fee_amount'             => 0,
                    'min_days_before_check_in' => false,
                    'max_days_before_check_in' => false,
                    'min_nights'             => false,
                    'max_nights'             => false,
                    'requires_amount'        => true,
                ),
                'fixed' => array(
                    'label'                  => __( 'Fixed amount off accommodation', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_FIXED,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_NONE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_NONE,
                    'room_amount'            => 'amount',
                    'service_amount'         => 0,
                    'fee_amount'             => 0,
                    'min_days_before_check_in' => false,
                    'max_days_before_check_in' => false,
                    'min_nights'             => false,
                    'max_nights'             => false,
                    'requires_amount'        => true,
                ),
                'early_bird' => array(
                    'label'                  => __( 'Early-bird percentage', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_PERCENTAGE,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_NONE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_NONE,
                    'room_amount'            => 'amount',
                    'service_amount'         => 0,
                    'fee_amount'             => 0,
                    'min_days_before_check_in' => true,
                    'max_days_before_check_in' => false,
                    'min_nights'             => false,
                    'max_nights'             => false,
                    'requires_amount'        => true,
                ),
                'last_minute' => array(
                    'label'                  => __( 'Last-minute percentage', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_PERCENTAGE,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_NONE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_NONE,
                    'room_amount'            => 'amount',
                    'service_amount'         => 0,
                    'fee_amount'             => 0,
                    'min_days_before_check_in' => false,
                    'max_days_before_check_in' => true,
                    'min_nights'             => false,
                    'max_nights'             => false,
                    'requires_amount'        => true,
                ),
                'long_stay' => array(
                    'label'                  => __( 'Long-stay percentage', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_PERCENTAGE,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_NONE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_NONE,
                    'room_amount'            => 'amount',
                    'service_amount'         => 0,
                    'fee_amount'             => 0,
                    'min_days_before_check_in' => false,
                    'max_days_before_check_in' => false,
                    'min_nights'             => true,
                    'max_nights'             => false,
                    'requires_amount'        => true,
                ),
                'free_stay' => array(
                    'label'                  => __( '100% off stay (rooms, services, fees)', 'loft1325-mobile-home' ),
                    'room_discount_type'     => $coupon::TYPE_ACCOMMODATION_PERCENTAGE,
                    'service_discount_type'  => $coupon::TYPE_SERVICE_PERCENTAGE,
                    'fee_discount_type'      => $coupon::TYPE_FEE_PERCENTAGE,
                    'room_amount'            => 100,
                    'service_amount'         => 100,
                    'fee_amount'             => 100,
                    'min_days_before_check_in' => false,
                    'max_days_before_check_in' => false,
                    'min_nights'             => false,
                    'max_nights'             => false,
                    'requires_amount'        => false,
                ),
            );
        }

        /**
         * Surface a helpful admin notice when dependencies are missing.
         */
        public function maybe_show_dependency_notice() {
            if ( $this->ensure_dependencies_ready() || ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            echo '<div class="notice notice-error"><p>' . esc_html__( 'Loft1325 Mobile Homepage needs the ND Booking plugin active to render properly. Please activate ND Booking before enabling the mobile experience.', 'loft1325-mobile-home' ) . '</p></div>';
        }

        /**
         * Ensure required dependencies are loaded before rendering the mobile homepage.
         *
         * @return bool
         */
        private function ensure_dependencies_ready() {
            if ( $this->dependencies_ready ) {
                return true;
            }

            $this->dependencies_ready = post_type_exists( 'nd_booking_cpt_1' );

            return $this->dependencies_ready;
        }

        /**
         * Determine whether the request is coming from a mobile or tablet device.
         *
         * @return bool
         */
        private function is_mobile_request() {
            $is_mobile = wp_is_mobile();

            if ( ! $is_mobile && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
                $user_agent = strtolower( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

                $mobile_indicators = array(
                    'iphone',
                    'ipod',
                    'ipad',
                    'android',
                    'blackberry',
                    'bb10',
                    'webos',
                    'windows phone',
                    'opera mini',
                    'mobile',
                    'tablet',
                );

                foreach ( $mobile_indicators as $indicator ) {
                    if ( false !== strpos( $user_agent, $indicator ) ) {
                        $is_mobile = true;
                        break;
                    }
                }

                if ( ! $is_mobile && false !== strpos( $user_agent, 'macintosh' ) && false !== strpos( $user_agent, 'mobile' ) ) {
                    $is_mobile = true;
                }
            }

            return (bool) apply_filters( 'loft1325_mobile_home_is_mobile', $is_mobile );
        }
    }
}

// Boot the plugin.
Loft1325_Mobile_Homepage::instance();
