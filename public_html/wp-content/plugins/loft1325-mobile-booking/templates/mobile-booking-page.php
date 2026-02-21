<?php
/**
 * Mobile reservation page template override.
 *
 * @package Loft1325\MobileBooking
 */

defined( 'ABSPATH' ) || exit;

$plugin      = Loft1325_Mobile_Booking::instance();
$booking_url = get_permalink();
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

	<section class="loft1325-mobile-booking__content">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>

	<footer class="loft1325-mobile-booking__finalize">
		<p><?php echo esc_html( $plugin->label( 'Vous avez trouvé le meilleur tarif.', 'You found the best rate.' ) ); ?></p>
		<a class="loft1325-mobile-booking__cta" href="<?php echo esc_url( $booking_url ); ?>#nd_booking_single_cpt_4_form_check_availability"><?php echo esc_html( $plugin->label( 'Finaliser', 'Finalize' ) ); ?></a>
	</footer>
</main>
<?php wp_footer(); ?>
</body>
</html>
