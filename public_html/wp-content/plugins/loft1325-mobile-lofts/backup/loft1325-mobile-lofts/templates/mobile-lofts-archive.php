<?php
/**
 * Mobile-only ND Booking loft archive template.
 *
 * @package Loft1325\MobileLofts
 */

defined( 'ABSPATH' ) || exit;

$plugin   = Loft1325_Mobile_Lofts::instance();
$language = $plugin->get_current_language();
$home_url = $plugin->localize_url( home_url( '/' ) );

$archive_title = $plugin->localize_label( 'Tous les lofts', 'All lofts' );
$archive_intro = $plugin->localize_label(
	'Choisissez votre loft signature et profitez d’un séjour au standing hôtelier, pensé pour le mobile.',
	'Choose your signature loft and enjoy a hotel-grade stay built for mobile ease.'
);
$cta_label = $plugin->localize_label( 'Voir le loft', 'View loft' );
$from_label = $plugin->localize_label( 'À partir de', 'From' );
$per_night  = $plugin->localize_label( 'par nuit', 'per night' );
$empty_price = $plugin->localize_label( 'Tarif sur demande', 'Rate on request' );
$back_label  = $plugin->localize_label( 'Retour accueil', 'Back home' );
$menu_label  = $plugin->localize_label( 'Ouvrir le menu', 'Open menu' );
$menu_close  = $plugin->localize_label( 'Fermer le menu', 'Close menu' );
$menu_title  = $plugin->localize_label( 'Menu', 'Menu' );
$language_label = $plugin->localize_label( 'Changer la langue', 'Change language' );

$lofts_query = new WP_Query(
	array(
		'post_type'      => 'nd_booking_cpt_1',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);
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
	<main id="loft1325-mobile-lofts-archive" class="loft1325-mobile-lofts-archive">
		<header class="header loft1325-mobile-header">
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
					<span class="language-toggle__label<?php echo 'fr' === $language ? ' is-active' : ''; ?>">FR</span>
					<span>·</span>
					<span class="language-toggle__label<?php echo 'en' === $language ? ' is-active' : ''; ?>">EN</span>
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

		<header class="loft1325-mobile-lofts-archive__topbar">
			<a class="loft1325-mobile-lofts-archive__crumb" href="<?php echo esc_url( $home_url ); ?>">
				<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
				<?php echo esc_html( $back_label ); ?>
			</a>
			<span class="loft1325-mobile-lofts-archive__badge"><?php echo esc_html( $plugin->localize_label( 'Sélection 5 étoiles', 'Five-star collection' ) ); ?></span>
		</header>

		<section class="loft1325-mobile-lofts-archive__hero">
			<h1><?php echo esc_html( $archive_title ); ?></h1>
			<p><?php echo esc_html( $archive_intro ); ?></p>
		</section>

		<section class="loft1325-mobile-lofts-archive__grid" aria-live="polite">
			<?php if ( $lofts_query->have_posts() ) : ?>
				<?php while ( $lofts_query->have_posts() ) : ?>
					<?php
					$lofts_query->the_post();
					$room_id   = get_the_ID();
					$room_data = $plugin->get_room_data( $room_id );
					$thumbnail_id = get_post_thumbnail_id( $room_id );
					$image_src = $thumbnail_id ? wp_get_attachment_image_src( $thumbnail_id, 'loft1325_mobile_loft_slider' ) : null;
					$image_url = $image_src ? $image_src[0] : '';

					if ( ! $image_url && ! empty( $room_data['hero_image'] ) ) {
						$image_url = $room_data['hero_image'];
					}

					$permalink = $plugin->localize_url( get_permalink( $room_id ) );
					?>
					<article class="loft1325-mobile-lofts-archive__card">
						<a class="loft1325-mobile-lofts-archive__card-link" href="<?php echo esc_url( $permalink ); ?>">
							<div class="loft1325-mobile-lofts-archive__media" aria-hidden="true">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $room_data['title'] ); ?>" loading="lazy" />
								<?php else : ?>
									<span class="loft1325-mobile-lofts-archive__media-fallback"></span>
								<?php endif; ?>
								<span class="loft1325-mobile-lofts-archive__pill"><?php echo esc_html( $plugin->localize_label( 'Arrivée autonome 24/7', 'Self check-in 24/7' ) ); ?></span>
							</div>
							<div class="loft1325-mobile-lofts-archive__content">
								<div class="loft1325-mobile-lofts-archive__meta">
									<?php if ( $room_data['branch_title'] ) : ?>
										<span><?php echo esc_html( $room_data['branch_title'] ); ?></span>
									<?php endif; ?>
									<?php if ( $room_data['branch_stars'] ) : ?>
										<div class="loft1325-mobile-lofts-archive__stars" aria-label="<?php echo esc_attr( sprintf( $plugin->localize_label( '%s étoiles', '%s stars' ), (string) $room_data['branch_stars'] ) ); ?>">
											<?php for ( $i = 0; $i < (int) $room_data['branch_stars']; $i++ ) : ?>
												<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
											<?php endfor; ?>
										</div>
									<?php endif; ?>
								</div>
								<h2><?php echo esc_html( $room_data['title'] ); ?></h2>
								<p><?php echo esc_html( $room_data['excerpt'] ); ?></p>
								<div class="loft1325-mobile-lofts-archive__chips">
									<?php if ( $room_data['capacity'] ) : ?>
										<span class="loft1325-mobile-lofts-archive__chip">
											<span class="dashicons dashicons-groups" aria-hidden="true"></span>
											<?php echo esc_html( $plugin->localize_label( 'Invités', 'Guests' ) ); ?> · <?php echo esc_html( $room_data['capacity'] ); ?>
										</span>
									<?php endif; ?>
									<?php if ( $room_data['room_size'] ) : ?>
										<span class="loft1325-mobile-lofts-archive__chip">
											<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
											<?php echo esc_html( $room_data['room_size'] ); ?> ㎡
										</span>
									<?php endif; ?>
								</div>
								<div class="loft1325-mobile-lofts-archive__price-row">
									<div>
										<span class="loft1325-mobile-lofts-archive__price-label"><?php echo esc_html( $from_label ); ?></span>
										<strong><?php echo $room_data['price'] ? esc_html( $room_data['price'] ) : esc_html( $empty_price ); ?></strong>
										<small><?php echo esc_html( $per_night ); ?></small>
									</div>
									<span class="loft1325-mobile-lofts-archive__cta"><?php echo esc_html( $cta_label ); ?></span>
								</div>
							</div>
						</a>
					</article>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="loft1325-mobile-lofts-archive__empty"><?php echo esc_html( $plugin->localize_label( 'Aucun loft n’est disponible pour le moment.', 'No lofts are available at the moment.' ) ); ?></p>
			<?php endif; ?>
		</section>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
