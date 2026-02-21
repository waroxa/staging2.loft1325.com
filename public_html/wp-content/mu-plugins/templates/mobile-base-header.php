<?php
/**
 * Loft1325 Mobile Base Header Template
 *
 * Shared template for /rooms/* + booking flow mobile pages.
 * Mirrors template-11 header structure/spacing.
 *
 * @var string $language
 * @var string $fr_url
 * @var string $en_url
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="header loft1325-mobile-base-header" aria-label="Loft1325 mobile header">
  <div class="header-inner loft1325-mobile-base-header__inner">
    <button class="icon-button loft1325-mobile-base-header__menu" type="button" aria-label="<?php echo esc_attr__( 'Ouvrir le menu', 'default' ); ?>">☰</button>

    <a class="logo loft1325-mobile-base-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
      <img
        src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png"
        srcset="https://loft1325.com/wp-content/uploads/2024/06/Asset-1-300x108.png 300w, https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png 518w"
        sizes="(max-width: 430px) 180px, 220px"
        alt="Lofts 1325"
      />
    </a>

    <div class="icon-button language-toggle loft1325-mobile-base-header__lang" aria-label="Language selector">
      <a href="<?php echo esc_url( $fr_url ); ?>" class="language-toggle__label<?php echo 'fr' === $language ? ' is-active' : ''; ?>">FR</a>
      <span class="language-toggle__separator">|</span>
      <a href="<?php echo esc_url( $en_url ); ?>" class="language-toggle__label<?php echo 'en' === $language ? ' is-active' : ''; ?>">EN</a>
    </div>
  </div>
</header>
