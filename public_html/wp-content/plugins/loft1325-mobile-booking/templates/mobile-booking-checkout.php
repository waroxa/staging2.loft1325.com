<?php
/**
 * Mobile checkout page template override.
 *
 * @package Loft1325\MobileBooking
 */

defined( 'ABSPATH' ) || exit;

$plugin      = Loft1325_Mobile_Booking::instance();
$home_url    = home_url( '/' );
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
	<section class="loft1325-mobile-booking__intro" aria-label="<?php echo esc_attr( $plugin->label( 'En-tête de réservation', 'Booking header' ) ); ?>">
		<a class="loft1325-mobile-booking__intro-back" href="<?php echo esc_url( $home_url ); ?>">
			&larr; <?php echo esc_html( $plugin->label( 'Retour accueil', 'Back home' ) ); ?>
		</a>
		<h1 class="loft1325-mobile-booking__intro-title"><?php echo esc_html( strtoupper( $plugin->label( 'Réservation', 'Reservation' ) ) ); ?></h1>
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
