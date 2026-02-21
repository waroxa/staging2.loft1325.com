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
$content     = preg_replace( '/<div\s+class="nd_booking_section\s+nd_booking_height_2\s+nd_booking_bg_grey"\s*><\/div>/i', '', (string) $content );
$content     = preg_replace( '/^\s*<div\s+class="nd_booking_section\s+nd_booking_height_(20|30|40)"\s*><\/div>/i', '', (string) $content );
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
		<h1 class="loft1325-mobile-booking__intro-title"><?php echo esc_html( strtoupper( $plugin->label( 'Paiement', 'Payment' ) ) ); ?></h1>
	</section>

	<section class="loft1325-mobile-booking__content loft1325-mobile-booking__content--checkout">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>

	
</main>
<?php wp_footer(); ?>
</body>
</html>
