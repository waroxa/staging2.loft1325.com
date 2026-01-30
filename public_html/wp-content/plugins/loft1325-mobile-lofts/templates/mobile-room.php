<?php
/**
 * Mobile-only ND Booking single room template.
 *
 * @package Loft1325\MobileLofts
 */

defined( 'ABSPATH' ) || exit;

$plugin         = Loft1325_Mobile_Lofts::instance();
$room_id        = get_the_ID();
$room_data      = $plugin->get_room_data( $room_id );
$gallery        = $plugin->get_room_gallery( $room_id );
$slider_markup  = $plugin->get_room_slider_markup( $room_id );
$booking_url    = $plugin->get_booking_url( $room_id );
$booking_action = function_exists( 'nd_booking_booking_page' ) ? nd_booking_booking_page() : $booking_url;
$booking_date_from = filter_input( INPUT_GET, 'nd_booking_archive_form_date_range_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$booking_date_to   = filter_input( INPUT_GET, 'nd_booking_archive_form_date_range_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$booking_guests    = filter_input( INPUT_GET, 'nd_booking_archive_form_guests', FILTER_SANITIZE_NUMBER_INT );
$booking_nights    = filter_input( INPUT_GET, 'nd_booking_archive_form_nights', FILTER_SANITIZE_NUMBER_INT );
$booking_guests    = $booking_guests ? absint( $booking_guests ) : 0;
$booking_nights    = $booking_nights ? absint( $booking_nights ) : 0;

if ( ! $booking_nights && $booking_date_from && $booking_date_to && function_exists( 'nd_booking_get_number_night' ) ) {
	$booking_nights = nd_booking_get_number_night( $booking_date_from, $booking_date_to );
}

if ( ! $booking_nights && ! $booking_date_from && ! $booking_date_to ) {
	$booking_nights = 1;
}

$room_id_room = get_post_meta( $room_id, 'nd_booking_id_room', true );
if ( ! $room_id_room ) {
	$room_id_room = $room_id;
}

$booking_form_id = $room_id . '-' . $room_id_room;
$has_booking_params = $booking_date_from && $booking_date_to;
$booking_payload = array_filter(
	array(
		'nd_booking_archive_form_date_range_from' => $booking_date_from,
		'nd_booking_archive_form_date_range_to'   => $booking_date_to,
		'nd_booking_archive_form_guests'          => $booking_guests ? $booking_guests : null,
		'nd_booking_archive_form_nights'          => $booking_nights ? $booking_nights : null,
	)
);

if ( ! $has_booking_params && $booking_nights ) {
	$booking_url = add_query_arg( 'nd_booking_archive_form_nights', $booking_nights, $booking_url );
}
$language       = $plugin->get_current_language();
$archive_url    = $plugin->get_lofts_archive_url();
$per_night      = $plugin->localize_label( 'par nuit', 'per night' );
$cta_label      = $plugin->localize_label( 'Réserver maintenant', 'Reserve now' );
$details_label  = $plugin->localize_label( 'Voir les détails', 'View details' );
$services_label = $plugin->localize_label( 'Services inclus', 'Included services' );
$extras_label   = $plugin->localize_label( 'Extras signature', 'Signature extras' );
$facts_label    = $plugin->localize_label( 'Moments clés', 'Highlights' );
$about_label    = $plugin->localize_label( 'À propos de ce loft', 'About this loft' );
$sleeps_label   = $plugin->localize_label( 'Invités max.', 'Max guests' );
$size_label     = $plugin->localize_label( 'Surface', 'Space' );
$nights_label   = $plugin->localize_label( 'Nuits minimales', 'Minimum nights' );
$branch_label   = $plugin->localize_label( 'Adresse', 'Location' );
$pill_label     = $plugin->localize_label( 'Arrivée autonome 24/7', 'Self check-in 24/7' );
$rating_label   = $plugin->localize_label( 'Expérience haut de gamme', 'High-touch experience' );
$empty_price    = $plugin->localize_label( 'Tarif sur demande', 'Rate on request' );
$vibe_label     = $plugin->localize_label( 'Ambiance signature', 'Signature vibe' );
$perks_label    = $plugin->localize_label( 'Avantages directs', 'Direct perks' );
$cta_hint       = $plugin->localize_label( 'Confirmation immédiate', 'Instant confirmation' );
$reviews_label  = $plugin->localize_label( 'Avis des voyageurs', 'Traveler reviews' );

$normal_services = $room_data['normal_services'] ?? array();
$extra_services  = $room_data['extra_services'] ?? array();
$normal_services = is_array( $normal_services ) ? $normal_services : array( $normal_services );
$extra_services  = is_array( $extra_services ) ? $extra_services : array( $extra_services );
$normal_services = array_values( array_filter( array_map( 'trim', $normal_services ) ) );
$extra_services  = array_values( array_filter( array_map( 'trim', $extra_services ) ) );
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
	<main id="loft1325-mobile-loftpage" class="loft1325-mobile-loft__backdrop">
		<div class="loft1325-mobile-loft">
			<header class="loft1325-mobile-loft__topbar">
				<a class="loft1325-mobile-loft__crumb" href="<?php echo esc_url( $archive_url ); ?>">
					<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
					<?php echo esc_html( $plugin->localize_label( 'Retour aux lofts', 'Back to lofts' ) ); ?>
				</a>
				<span class="loft1325-mobile-loft__pill"><?php echo esc_html( $pill_label ); ?></span>
			</header>

			<section class="loft1325-mobile-loft__hero" aria-label="<?php the_title_attribute(); ?>">
				<?php if ( $slider_markup ) : ?>
					<div class="loft1325-mobile-loft__slider loft1325-mobile-loft__slider--rev">
						<?php echo $slider_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<div class="loft1325-mobile-loft__slider" data-loft-slider data-autoplay="true">
						<div class="loft1325-mobile-loft__slider-track" data-loft-slider-track style="--loft-slider-count: <?php echo esc_attr( max( 1, count( $gallery ) ) ); ?>;">
							<?php if ( ! empty( $gallery ) ) : ?>
								<?php foreach ( $gallery as $image ) : ?>
									<figure class="loft1325-mobile-loft__slide" data-loft-slide>
										<?php if ( ! empty( $image['url'] ) ) : ?>
											<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ? $image['alt'] : $room_data['title'] ); ?>" loading="lazy" />
										<?php endif; ?>
									</figure>
								<?php endforeach; ?>
							<?php else : ?>
								<figure class="loft1325-mobile-loft__slide loft1325-mobile-loft__slide--placeholder" data-loft-slide>
									<div class="loft1325-mobile-loft__slide-fallback" aria-hidden="true"></div>
								</figure>
							<?php endif; ?>
						</div>

						<?php if ( count( $gallery ) > 1 ) : ?>
							<div class="loft1325-mobile-loft__slider-nav" aria-label="<?php echo esc_attr( $plugin->localize_label( 'Navigation du carrousel', 'Carousel navigation' ) ); ?>">
								<button class="loft1325-mobile-loft__slider-btn" type="button" data-loft-prev aria-label="<?php echo esc_attr( $plugin->localize_label( 'Image précédente', 'Previous image' ) ); ?>">
									<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
								</button>
								<button class="loft1325-mobile-loft__slider-btn" type="button" data-loft-next aria-label="<?php echo esc_attr( $plugin->localize_label( 'Image suivante', 'Next image' ) ); ?>">
									<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
								</button>
							</div>
							<div class="loft1325-mobile-loft__slider-progress" aria-hidden="true">
								<span class="loft1325-mobile-loft__slider-progress-fill" data-loft-progress></span>
							</div>
							<div class="loft1325-mobile-loft__slider-dots" role="tablist" aria-label="<?php echo esc_attr( $plugin->localize_label( 'Sélectionner une image', 'Select an image' ) ); ?>" data-loft-dots></div>
						<?php endif; ?>

						<div class="loft1325-mobile-loft__slider-badge">
							<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
							<?php echo esc_html( $rating_label ); ?>
							<span class="loft1325-mobile-loft__slider-count" data-loft-counter></span>
						</div>
					</div>
				<?php endif; ?>

				<div class="loft1325-mobile-loft__hero-card loft1325-mobile-loft__section">
					<div class="loft1325-mobile-loft__hero-meta">
						<?php if ( $room_data['branch_title'] ) : ?>
							<span class="loft1325-mobile-loft__eyebrow"><?php echo esc_html( $room_data['branch_title'] ); ?></span>
						<?php endif; ?>
						<?php if ( $room_data['branch_stars'] ) : ?>
							<div class="loft1325-mobile-loft__stars" aria-label="<?php echo esc_attr( sprintf( $plugin->localize_label( '%s étoiles', '%s stars' ), (string) $room_data['branch_stars'] ) ); ?>">
								<?php for ( $i = 0; $i < (int) $room_data['branch_stars']; $i++ ) : ?>
									<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
								<?php endfor; ?>
							</div>
						<?php endif; ?>
					</div>

					<h1 class="loft1325-mobile-loft__title"><?php echo esc_html( $room_data['title'] ); ?></h1>
					<p class="loft1325-mobile-loft__lede"><?php echo esc_html( $room_data['excerpt'] ); ?></p>

					<div class="loft1325-mobile-loft__microgrid" role="list">
						<?php if ( $room_data['capacity'] ) : ?>
							<div class="loft1325-mobile-loft__microchip" role="listitem">
								<span class="dashicons dashicons-groups" aria-hidden="true"></span>
								<span><?php echo esc_html( $sleeps_label ); ?> · <?php echo esc_html( $room_data['capacity'] ); ?></span>
							</div>
						<?php endif; ?>
						<?php if ( $room_data['room_size'] ) : ?>
							<div class="loft1325-mobile-loft__microchip" role="listitem">
								<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
								<span><?php echo esc_html( $room_data['room_size'] ); ?> ㎡</span>
							</div>
						<?php endif; ?>
						<div class="loft1325-mobile-loft__microchip" role="listitem">
							<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
							<span><?php echo esc_html( $vibe_label ); ?></span>
						</div>
					</div>

					<div class="loft1325-mobile-loft__price">
						<div class="loft1325-mobile-loft__price-label"><?php echo esc_html( $plugin->localize_label( 'À partir de', 'From' ) ); ?></div>
						<div class="loft1325-mobile-loft__price-value">
							<strong><?php echo $room_data['price'] ? esc_html( $room_data['price'] ) : esc_html( $empty_price ); ?></strong>
							<small><?php echo esc_html( $per_night ); ?></small>
						</div>
						<span class="loft1325-mobile-loft__price-hint"><?php echo esc_html( $cta_hint ); ?></span>
					</div>

					<div class="loft1325-mobile-loft__reviews">
						<h2><?php echo esc_html( $reviews_label ); ?></h2>
						<?php echo do_shortcode( '[trustindex no-registration=google]' ); ?>
					</div>

					<div class="loft1325-mobile-loft__cta-row">
						<a class="loft1325-mobile-loft__btn loft1325-mobile-loft__btn--ghost" href="#loft1325-mobile-loft-highlights">
							<?php echo esc_html( $details_label ); ?>
						</a>
					</div>
				</div>
			</section>

			<section class="loft1325-mobile-loft__section loft1325-mobile-loft__section--perks">
				<div class="loft1325-mobile-loft__section-header">
					<span class="loft1325-mobile-loft__section-pill"><?php echo esc_html( $perks_label ); ?></span>
					<h2><?php echo esc_html( $plugin->localize_label( 'Votre confort en priorité', 'Your stay, elevated' ) ); ?></h2>
				</div>
				<div class="loft1325-mobile-loft__perk-grid" role="list">
					<div class="loft1325-mobile-loft__perk" role="listitem">
						<span class="dashicons dashicons-universal-access" aria-hidden="true"></span>
						<div>
							<p><?php echo esc_html( $plugin->localize_label( 'Entrée simplifiée', 'Frictionless arrival' ) ); ?></p>
							<small><?php echo esc_html( $plugin->localize_label( 'Check-in autonome 24/7 et guidé.', 'Guided self check-in 24/7.' ) ); ?></small>
						</div>
					</div>
					<div class="loft1325-mobile-loft__perk" role="listitem">
						<span class="dashicons dashicons-format-gallery" aria-hidden="true"></span>
						<div>
							<p><?php echo esc_html( $plugin->localize_label( 'Ambiance loft spacieuse', 'Spacious loft ambiance' ) ); ?></p>
							<small><?php echo esc_html( $plugin->localize_label( 'Volumes ouverts, coins détente et cuisine équipée.', 'Open volumes, lounge corners, and an equipped kitchen.' ) ); ?></small>
						</div>
					</div>
					<div class="loft1325-mobile-loft__perk" role="listitem">
						<span class="dashicons dashicons-heart" aria-hidden="true"></span>
						<div>
							<p><?php echo esc_html( $plugin->localize_label( 'Confort hôtelier', 'Hotel-grade comfort' ) ); ?></p>
							<small><?php echo esc_html( $plugin->localize_label( 'Literie premium, ambiance douce.', 'Premium bedding and soft lighting.' ) ); ?></small>
						</div>
					</div>
				</div>
			</section>

			<section id="loft1325-mobile-loft-highlights" class="loft1325-mobile-loft__section loft1325-mobile-loft__section--cards">
				<div class="loft1325-mobile-loft__section-header">
					<span class="loft1325-mobile-loft__section-pill"><?php echo esc_html( $plugin->localize_label( 'Faits saillants', 'Key details' ) ); ?></span>
					<h2><?php echo esc_html( $facts_label ); ?></h2>
				</div>
				<div class="loft1325-mobile-loft__fact-grid" role="list">
					<?php if ( $room_data['capacity'] ) : ?>
						<div class="loft1325-mobile-loft__fact" role="listitem">
							<span class="dashicons dashicons-groups" aria-hidden="true"></span>
							<div>
								<p><?php echo esc_html( $sleeps_label ); ?></p>
								<strong><?php echo esc_html( $room_data['capacity'] ); ?></strong>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $room_data['room_size'] ) : ?>
						<div class="loft1325-mobile-loft__fact" role="listitem">
							<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
							<div>
								<p><?php echo esc_html( $size_label ); ?></p>
								<strong><?php echo esc_html( $room_data['room_size'] ); ?> ㎡</strong>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $room_data['min_nights'] ) : ?>
						<div class="loft1325-mobile-loft__fact" role="listitem">
							<span class="dashicons dashicons-clock" aria-hidden="true"></span>
							<div>
								<p><?php echo esc_html( $nights_label ); ?></p>
								<strong><?php echo esc_html( $room_data['min_nights'] ); ?></strong>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $room_data['branch_title'] ) : ?>
						<div class="loft1325-mobile-loft__fact" role="listitem">
							<span class="dashicons dashicons-location-alt" aria-hidden="true"></span>
							<div>
								<p><?php echo esc_html( $branch_label ); ?></p>
								<strong><?php echo esc_html( $room_data['branch_title'] ); ?></strong>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<?php if ( ! empty( $normal_services ) || ! empty( $extra_services ) ) : ?>
				<section class="loft1325-mobile-loft__section loft1325-mobile-loft__section--stacked">
					<div class="loft1325-mobile-loft__section-header">
						<span class="loft1325-mobile-loft__section-pill"><?php echo esc_html( $plugin->localize_label( 'Pensé pour votre séjour', 'Designed for your stay' ) ); ?></span>
						<h2><?php echo esc_html( $services_label ); ?></h2>
					</div>

					<?php if ( ! empty( $normal_services ) ) : ?>
						<ul class="loft1325-mobile-loft__chip-list" aria-label="<?php echo esc_attr( $services_label ); ?>">
							<?php foreach ( $normal_services as $service ) : ?>
								<?php
								$service_label = str_replace( array( '-', '_' ), ' ', $service );
								$service_label = ucwords( $service_label );
								?>
								<li class="loft1325-mobile-loft__chip"><?php echo esc_html( $service_label ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( ! empty( $extra_services ) ) : ?>
						<div class="loft1325-mobile-loft__section-subheader">
							<h3><?php echo esc_html( $extras_label ); ?></h3>
						</div>
						<ul class="loft1325-mobile-loft__chip-list loft1325-mobile-loft__chip-list--accent" aria-label="<?php echo esc_attr( $extras_label ); ?>">
							<?php foreach ( $extra_services as $service ) : ?>
								<?php
								$service_label = str_replace( array( '-', '_' ), ' ', $service );
								$service_label = ucwords( $service_label );
								?>
								<li class="loft1325-mobile-loft__chip loft1325-mobile-loft__chip--glow"><?php echo esc_html( $service_label ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<section class="loft1325-mobile-loft__section loft1325-mobile-loft__section--content">
				<div class="loft1325-mobile-loft__section-header">
					<span class="loft1325-mobile-loft__section-pill"><?php echo esc_html( $plugin->localize_label( 'Immersion', 'In-depth look' ) ); ?></span>
					<h2><?php echo esc_html( $about_label ); ?></h2>
				</div>
				<div class="loft1325-mobile-loft__content">
					<?php echo $room_data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</section>

			<div class="loft1325-mobile-loft__floating-bar">
				<div class="loft1325-mobile-loft__floating-price">
					<span class="loft1325-mobile-loft__floating-label"><?php echo esc_html( $plugin->localize_label( 'À partir de', 'From' ) ); ?></span>
					<strong><?php echo $room_data['price'] ? esc_html( $room_data['price'] ) : esc_html( $empty_price ); ?></strong>
					<small><?php echo esc_html( $per_night ); ?></small>
				</div>
				<?php if ( $has_booking_params && $booking_action ) : ?>
					<form class="loft1325-mobile-loft__booking-form" action="<?php echo esc_url( $booking_action ); ?>" method="post">
						<input type="hidden" name="nd_booking_form_booking_arrive_advs" value="1" />
						<input type="hidden" name="nd_booking_form_booking_arrive_sr" value="1" />
						<input type="hidden" name="nd_booking_archive_form_id" value="<?php echo esc_attr( $booking_form_id ); ?>" />
						<?php foreach ( $booking_payload as $field => $value ) : ?>
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" />
						<?php endforeach; ?>
						<button class="loft1325-mobile-loft__btn loft1325-mobile-loft__btn--primary" type="submit">
							<?php echo esc_html( $plugin->localize_label( 'Réserver', 'Reserve' ) ); ?>
						</button>
					</form>
				<?php else : ?>
					<a class="loft1325-mobile-loft__btn loft1325-mobile-loft__btn--primary" href="<?php echo esc_url( $booking_url ); ?>">
						<?php echo esc_html( $plugin->localize_label( 'Réserver', 'Reserve' ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
