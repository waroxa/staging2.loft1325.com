<?php
/**
 * Mobile checkout page template override.
 *
 * @package Loft1325\MobileBooking
 */

defined( 'ABSPATH' ) || exit;

$plugin      = Loft1325_Mobile_Booking::instance();
$language    = $plugin->get_language();
$home_url    = home_url( $language === 'en' ? '/en/' : '/' );
$content     = apply_filters( 'the_content', (string) get_post_field( 'post_content', get_the_ID() ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<main id="loft1325-mobile-booking" class="loft1325-mobile-booking">
	<header class="header loft1325-mobile-header">
		<div class="header-inner">
			<button class="icon-button" type="button" id="openMenu" aria-label="<?php echo esc_attr( $plugin->label( 'Ouvrir le menu', 'Open menu' ) ); ?>">≡</button>
			<img class="logo" src="https://loft1325.com/wp-content/uploads/2024/06/Asset-1.png" alt="Lofts 1325" />
			<button class="icon-button" type="button" id="openMenuRight" aria-label="<?php echo esc_attr( $plugin->label( 'Options', 'Options' ) ); ?>">⋯</button>
		</div>
	</header>

	<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
		<div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle">
			<div class="mobile-menu__header">
				<p class="mobile-menu__title" id="mobileMenuTitle"><?php echo esc_html( $plugin->label( 'Menu', 'Menu' ) ); ?></p>
				<button class="mobile-menu__close" type="button" id="closeMenu" aria-label="<?php echo esc_attr( $plugin->label( 'Fermer le menu', 'Close menu' ) ); ?>">×</button>
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

	<section class="loft1325-mobile-booking__hero">
		<a href="<?php echo esc_url( $home_url ); ?>" class="loft1325-mobile-booking__crumb">← <?php echo esc_html( $plugin->label( 'Retour accueil', 'Back home' ) ); ?></a>
		<h1><?php echo esc_html( $plugin->label( 'Paiement', 'Checkout' ) ); ?></h1>
	</section>

	<section class="loft1325-mobile-booking__content loft1325-mobile-booking__content--checkout">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>

	<footer class="loft1325-mobile-booking__finalize">
		<p><?php echo esc_html( $plugin->label( 'Réservation sécurisée.', 'Secure reservation.' ) ); ?></p>
		<button class="loft1325-mobile-booking__cta" type="button" onclick="window.scrollTo({top:0,behavior:'smooth'});"><?php echo esc_html( $plugin->label( 'Finaliser', 'Finalize' ) ); ?></button>
	</footer>
</main>
<?php wp_footer(); ?>
</body>
</html>
