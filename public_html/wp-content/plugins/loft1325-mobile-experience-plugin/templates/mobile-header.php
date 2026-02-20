<?php
/**
 * Mobile Header Template
 *
 * This template renders the consistent mobile header for the Loft1325 Mobile Experience plugin.
 */

$locale   = get_locale();
$language = strpos( $locale, 'en_' ) === 0 ? 'en' : 'fr';
$strings  = array(
    'menu_label'             => $language === 'en' ? 'Open menu' : 'Ouvrir le menu',
    'menu_close'             => $language === 'en' ? 'Close menu' : 'Fermer le menu',
    'menu_title'             => $language === 'en' ? 'Menu' : 'Menu',
);

?><!DOCTYPE html>
<html lang="<?php echo esc_attr( $language ); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lofts 1325 · Mobile</title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="mobile-shell">

  <header class="header">
    <div class="header-inner">
      <button class="menu-toggle" aria-label="<?php echo esc_attr( $strings['menu_label'] ); ?>">
        <span class="menu-icon"></span>
      </button>
      <div class="logo">
        <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/logo.svg' ); ?>" alt="Loft1325 Logo" />
      </div>
      <div class="language-switcher">
        <a href="?lang=fr" class="<?php echo $language === 'fr' ? 'active' : ''; ?>">FR</a>
        <span>·</span>
        <a href="?lang=en" class="<?php echo $language === 'en' ? 'active' : ''; ?>">EN</a>
      </div>
    </div>
  </header>

  <nav class="mobile-menu" aria-hidden="true">
    <div class="mobile-menu-header">
      <button class="menu-close" aria-label="<?php echo esc_attr( $strings['menu_close'] ); ?>"></button>
    </div>
    <div class="mobile-menu-inner">
      <h3><?php echo esc_html( $strings['menu_title'] ); ?></h3>
      <?php
      wp_nav_menu( array(
          'theme_location' => 'mobile-menu',
          'container'      => false,
          'menu_class'     => 'mobile-menu-list',
          'depth'          => 1,
      ) );
      ?>
    </div>
  </nav>

  <div class="mobile-menu-overlay"></div>

  <main class="main-content">
