<?php
/*
Plugin Name:       Hotel Booking
Description:       The plugin is used to manage your booking. To get started: 1) Click the "Activate" link to the left of this description. 2) Follow the documentation for installation for use the plugin in the better way.
Version:           99999.9
Plugin URI:        https://nicdark.com
Author:            Nicdark
Author URI:        https://nicdark.com
License:           GPLv2 or later
*/

///////////////////////////////////////////////////TRANSLATIONS///////////////////////////////////////////////////////////////

//translation
function nd_booking_load_textdomain()
{
  load_plugin_textdomain("nd-booking", false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nd_booking_load_textdomain');


///////////////////////////////////////////////////DB///////////////////////////////////////////////////////////////
register_activation_hook( __FILE__, 'nd_booking_create_booking_db' );

function nd_booking_get_booking_table_schema() {
    global $wpdb;

    $nd_booking_table_name = $wpdb->prefix . 'nd_booking_booking';

    return "CREATE TABLE $nd_booking_table_name (
      id int(11) NOT NULL AUTO_INCREMENT,
      id_post int(11) NOT NULL,
      title_post varchar(255) NOT NULL,
      date varchar(255) NOT NULL,
      date_from varchar(255) NOT NULL,
      date_to varchar(255) NOT NULL,
      guests int(11) NOT NULL,
      final_trip_price decimal(12,2) NOT NULL,
      extra_services varchar(255) NOT NULL,
      id_user int(11) NOT NULL,
      user_first_name varchar(255) NOT NULL,
      user_last_name varchar(255) NOT NULL,
      paypal_email varchar(255) NOT NULL,
      user_phone varchar(255) NOT NULL,
      user_address varchar(255) NOT NULL,
      user_city varchar(255) NOT NULL,
      user_country varchar(255) NOT NULL,
      user_message varchar(255) NOT NULL,
      user_arrival varchar(255) NOT NULL,
      user_coupon varchar(255) NOT NULL,
      paypal_payment_status varchar(255) NOT NULL,
      paypal_currency varchar(255) NOT NULL,
      paypal_tx varchar(255) NOT NULL,
      action_type varchar(255) NOT NULL,
      UNIQUE KEY id (id)
    );";
}

function nd_booking_create_booking_db() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( nd_booking_get_booking_table_schema() );
}

function nd_booking_maybe_upgrade_booking_db() {
    global $wpdb;

    $nd_booking_table_name = $wpdb->prefix . 'nd_booking_booking';

    $table_exists = $wpdb->get_var(
        $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $nd_booking_table_name ) )
    );

    if ( $table_exists !== $nd_booking_table_name ) {
        return;
    }

    $final_trip_price_column = $wpdb->get_row(
        $wpdb->prepare( "SHOW COLUMNS FROM $nd_booking_table_name LIKE %s", 'final_trip_price' )
    );

    if ( empty( $final_trip_price_column ) || false !== stripos( $final_trip_price_column->Type, 'decimal' ) ) {
        return;
    }

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( nd_booking_get_booking_table_schema() );
}
add_action( 'plugins_loaded', 'nd_booking_maybe_upgrade_booking_db' );



///////////////////////////////////////////////////CSS STYLE///////////////////////////////////////////////////////////////

//add custom css
function nd_booking_scripts() {
  
  //basic css plugin
  wp_enqueue_style( 'nd_booking_style', esc_url(plugins_url('assets/css/style.css', __FILE__ )) );

  //mobile booking flow polish
  wp_enqueue_style( 'nd_booking_mobile_flow', esc_url(plugins_url('assets/css/mobile-booking-flow.css', __FILE__ )), array( 'nd_booking_style' ), false, false );

  if ( nd_booking_is_mobile_flow_room_selection() ) {
    $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/mobile-booking-flow.js';
    $script_uri  = plugins_url( 'assets/js/mobile-booking-flow.js', __FILE__ );
    $script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0';
    wp_enqueue_script( 'nd_booking_mobile_flow', esc_url( $script_uri ), array(), $script_ver, true );
  }

  wp_enqueue_script('jquery');
  
}
add_action( 'wp_enqueue_scripts', 'nd_booking_scripts' );


/**
 * Add body classes for ND Booking flow pages to enable scoped styling.
 *
 * @param array $classes Existing body classes.
 *
 * @return array
 */
function nd_booking_add_flow_body_classes( $classes ) {

  if ( is_admin() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
    return $classes;
  }

  $request_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );

  if ( '' === $request_path ) {
    return $classes;
  }

  $normalized_path = trim( trailingslashit( $request_path ), '/' );
  $normalized_path = preg_replace( '#^[a-z]{2}/#', '', $normalized_path );

  if ( 'nd-booking-pages/nd-booking-page' === $normalized_path ) {
    $classes[] = 'nd-booking-flow-page';
    $classes[] = 'nd-booking-flow-room-selection';
  }

  if ( 'nd-booking-pages/nd-booking-checkout' === $normalized_path ) {
    $classes[] = 'nd-booking-flow-page';
    $classes[] = 'nd-booking-flow-checkout';
  }

  return $classes;
}
add_filter( 'body_class', 'nd_booking_add_flow_body_classes' );

/**
 * Determine if the current request is the ND Booking room selection page on mobile.
 *
 * @return bool
 */
function nd_booking_is_mobile_flow_room_selection() {
  if ( is_admin() || is_feed() || is_embed() ) {
    return false;
  }

  if ( ! wp_is_mobile() ) {
    return false;
  }

  $booking_page_id = (int) get_option( 'nd_booking_booking_page' );

  if ( $booking_page_id && is_page( $booking_page_id ) ) {
    return true;
  }

  if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
    return false;
  }

  $request_path = (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );

  if ( '' === $request_path ) {
    return false;
  }

  $normalized_path = trim( trailingslashit( $request_path ), '/' );
  $normalized_path = preg_replace( '#^[a-z]{2}/#', '', $normalized_path );

  return 'nd-booking-pages/nd-booking-page' === $normalized_path;
}

/**
 * Render the mobile header for the booking flow page.
 */
function nd_booking_render_mobile_flow_header() {
  if ( ! nd_booking_is_mobile_flow_room_selection() ) {
    return;
  }

  $language = 'fr';
  if ( function_exists( 'trp_get_current_language' ) ) {
    $language = (string) trp_get_current_language();
  } else {
    $language = function_exists( 'determine_locale' ) ? (string) determine_locale() : get_locale();
  }

  $language = strtolower( substr( $language, 0, 2 ) );
  $language = ( 'en' === $language ) ? 'en' : 'fr';

  $menu_label = ( 'en' === $language ) ? 'Open menu' : 'Ouvrir le menu';
  $menu_close = ( 'en' === $language ) ? 'Close menu' : 'Fermer le menu';
  $menu_title = ( 'en' === $language ) ? 'Menu' : 'Menu';
  $language_label = ( 'en' === $language ) ? 'Change language' : 'Changer la langue';
  ?>
  <header class="header loft1325-mobile-booking-header">
    <div class="header-inner">
      <button class="icon-button" type="button" id="openMenu" aria-label="<?php echo esc_attr( $menu_label ); ?>">≡</button>
      <img
        class="logo"
        src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
        srcset="https://loft1325.com/wp-content/uploads/2024/06/Asset-1-300x108.png 300w, https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png 518w"
        sizes="(max-width: 430px) 180px, 220px"
        alt="Lofts 1325"
      />
      <button class="icon-button language-toggle" type="button" id="headerLanguageToggle" aria-label="<?php echo esc_attr( $language_label ); ?>">
        <span class="language-toggle__label<?php echo ( 'fr' === $language ) ? ' is-active' : ''; ?>">FR</span>
        <span>·</span>
        <span class="language-toggle__label<?php echo ( 'en' === $language ) ? ' is-active' : ''; ?>">EN</span>
      </button>
    </div>
  </header>

  <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle">
      <div class="mobile-menu__header">
        <p class="mobile-menu__title" id="mobileMenuTitle"><?php echo esc_html( $menu_title ); ?></p>
        <button class="mobile-menu__close" type="button" id="closeMenu" aria-label="<?php echo esc_attr( $menu_close ); ?>">×</button>
      </div>
      <?php
      echo wp_nav_menu(
        array(
          'theme_location' => 'main-menu',
          'container'      => false,
          'menu_class'     => 'mobile-menu__list',
          'fallback_cb'    => 'wp_page_menu',
          'echo'           => false,
        )
      );
      ?>
    </div>
  </div>
  <?php
}
add_action( 'wp_body_open', 'nd_booking_render_mobile_flow_header' );


//START add admin custom css
function nd_booking_admin_style() {
  
  wp_enqueue_style( 'nd_booking_admin_style', esc_url(plugins_url('assets/css/admin-style.css', __FILE__ )), array(), false, false );
  
}
add_action( 'admin_enqueue_scripts', 'nd_booking_admin_style' );
//END add custom css


///////////////////////////////////////////////////GET TEMPLATE ///////////////////////////////////////////////////////////////

//single Cpt 1
function nd_booking_get_cpt_1_template($nd_booking_single_cpt_1_template) {
     global $post;

     if ($post->post_type == 'nd_booking_cpt_1') {
          $nd_booking_single_cpt_1_template = dirname( __FILE__ ) . '/templates/single-cpt-1.php';
     }
     return $nd_booking_single_cpt_1_template;
}
add_filter( 'single_template', 'nd_booking_get_cpt_1_template' );

//single Cpt 4
function nd_booking_get_cpt_4_template($nd_booking_single_cpt_4_template) {
     global $post;

     if ($post->post_type == 'nd_booking_cpt_4') {
          $nd_booking_single_cpt_4_template = dirname( __FILE__ ) . '/templates/single-cpt-4.php';
     }
     return $nd_booking_single_cpt_4_template;
}
add_filter( 'single_template', 'nd_booking_get_cpt_4_template' );

//update theme options
function nd_booking_theme_setup_update(){
    update_option( 'nicdark_theme_author', 0 );
}
add_action( 'after_switch_theme' , 'nd_booking_theme_setup_update');


///////////////////////////////////////////////////CPT///////////////////////////////////////////////////////////////
foreach ( glob ( plugin_dir_path( __FILE__ ) . "inc/cpt/*.php" ) as $file ){
  include_once realpath($file);
}


///////////////////////////////////////////////////SHORTCODES ///////////////////////////////////////////////////////////////
foreach ( glob ( plugin_dir_path( __FILE__ ) . "inc/shortcodes/*.php" ) as $file ){
  include_once realpath($file);
}


///////////////////////////////////////////////////ADDONS ///////////////////////////////////////////////////////////////
foreach ( glob ( plugin_dir_path( __FILE__ ) . "addons/*/index.php" ) as $file ){
  include_once realpath($file);
}


///////////////////////////////////////////////////FUNCTIONS///////////////////////////////////////////////////////////////
require_once dirname( __FILE__ ) . '/inc/functions/functions.php';


///////////////////////////////////////////////////METABOX ///////////////////////////////////////////////////////////////
foreach ( glob ( plugin_dir_path( __FILE__ ) . "inc/metabox/*.php" ) as $file ){
  include_once realpath($file);
}


///////////////////////////////////////////////////PLUGIN SETTINGS ///////////////////////////////////////////////////////////
require_once dirname( __FILE__ ) . '/inc/admin/plugin-settings.php';


//function for get plugin version
function nd_booking_get_plugin_version(){

    $nd_booking_plugin_data = get_plugin_data( __FILE__ );
    $nd_booking_plugin_version = $nd_booking_plugin_data['Version'];

    return $nd_booking_plugin_version;

}


