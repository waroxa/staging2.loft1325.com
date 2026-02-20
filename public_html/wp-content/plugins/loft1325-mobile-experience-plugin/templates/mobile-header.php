<?php
/**
 * Mobile Header Template
 * 
 * This template renders the standardized mobile header with menu toggle,
 * logo, and language selector.
 */

// Get current language (assuming WPML or similar is in use)
$current_language = get_locale();
$is_french = strpos( $current_language, 'fr' ) !== false;
?>

<header class="mobile-header">
    <button class="menu-toggle" id="mobile-menu-toggle" aria-label="Toggle menu">
        <span>☰</span>
    </button>
    
    <div class="logo">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <img src="<?php echo esc_url( plugins_url( '../assets/images/logo.svg', __FILE__ ) ); ?>" alt="Lofts 1325" style="height: 40px;">
        </a>
    </div>
    
    <div class="language-toggle">
        <a href="<?php echo esc_url( add_query_arg( 'lang', 'fr' ) ); ?>" class="<?php echo $is_french ? 'active' : ''; ?>">FR</a>
        <span>·</span>
        <a href="<?php echo esc_url( add_query_arg( 'lang', 'en' ) ); ?>" class="<?php echo ! $is_french ? 'active' : ''; ?>">EN</a>
    </div>
</header>
