<?php
/**
 * Mobile Menu Template
 * 
 * This template renders the full-screen mobile menu overlay.
 */
?>

<nav class="mobile-menu" id="mobile-menu">
    <button class="close-btn" id="mobile-menu-close" aria-label="Close menu">✕</button>
    
    <div class="logo">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <img src="<?php echo esc_url( plugins_url( 'assets/images/logo.svg', dirname( __FILE__ ) ) ); ?>" alt="Lofts 1325" style="height: 40px;">
        </a>
    </div>
    
    <ul>
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'ACCUEIL', 'loft1325-mobile' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/nos-lofts' ) ); ?>"><?php esc_html_e( 'NOS LOFTS', 'loft1325-mobile' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/notre-histoire' ) ); ?>"><?php esc_html_e( 'NOTRE HISTOIRE', 'loft1325-mobile' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/support' ) ); ?>"><?php esc_html_e( 'SUPPORT', 'loft1325-mobile' ); ?></a></li>
        <li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'MON COMPTE', 'loft1325-mobile' ); ?></a></li>
    </ul>
    
    <div class="footer-links">
        <div class="language-toggle">
            <a href="<?php echo esc_url( add_query_arg( 'lang', 'fr' ) ); ?>">FR</a>
            <span>|</span>
            <a href="<?php echo esc_url( add_query_arg( 'lang', 'en' ) ); ?>">EN</a>
        </div>
        
        <div class="social-icons">
            <a href="https://instagram.com/loft1325" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <span>📷</span>
            </a>
            <a href="https://facebook.com/loft1325" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <span>f</span>
            </a>
        </div>
    </div>
</nav>
