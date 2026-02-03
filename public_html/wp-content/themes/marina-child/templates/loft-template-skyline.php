<?php
/*
Template Name: Loft 1325 Mobile - Skyline
*/

get_header();

$hero_slides = array(
    // Replace these images to update the hero slider.
    'https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?auto=format&fit=crop&w=1400&q=80',
);

$friends_photo = 'https://images.unsplash.com/photo-1511988617509-a57c8a288659?auto=format&fit=crop&w=1200&q=80';
$booking_url   = home_url( '/rooms/' );
$payment_url   = home_url( '/checkout/' );
?>

<div class="loft-template loft-template--skyline">
    <div class="loft-topbar">
        <div class="loft-topbar__brand">
            Loft 1325 Skyline
            <span>Virtual hotel, real wow</span>
        </div>
        <a class="loft-topbar__cta" href="<?php echo esc_url( $booking_url ); ?>">Shop</a>
    </div>

    <div class="loft-section" data-loft-slider data-images='<?php echo wp_json_encode( $hero_slides ); ?>' data-interval="6000">
        <div class="loft-hero">
            <div class="loft-hero__media" style="background-image: url('<?php echo esc_url( $hero_slides[0] ); ?>');"></div>
            <div class="loft-hero__overlay">
                <div class="loft-hero__badge">Skyline edit</div>
                <h1 class="loft-hero__title">Bright lofts for bold weekends.</h1>
                <p class="loft-hero__subtitle">Designed for luxury guests who want zero friction.</p>
                <div class="loft-footer-actions" style="margin-top: 12px;">
                    <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Book in seconds</a>
                </div>
            </div>
            <div class="loft-hero__nav">
                <button class="loft-hero__button" type="button" data-loft-prev aria-label="Previous slide">‹</button>
                <button class="loft-hero__button" type="button" data-loft-next aria-label="Next slide">›</button>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-card">
            <div class="loft-label">Why it wows</div>
            <h2 class="loft-title">Self-serve, but elevated.</h2>
            <p class="loft-text">Guests unlock with their phone, split bills instantly, and get a curated stay guide.</p>
        </div>

        <div class="loft-card">
            <div class="loft-grid">
                <div>
                    <div class="loft-label">Arrival vibe</div>
                    <p class="loft-text">Mood lighting + spa-ready bathrooms.</p>
                </div>
                <div>
                    <div class="loft-label">Departure ease</div>
                    <p class="loft-text">Pay and go, no waiting in line.</p>
                </div>
            </div>
            <div class="loft-divider"></div>
            <div class="loft-footer-actions">
                <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Explore lofts</a>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-image-card" style="background-image: url('<?php echo esc_url( $friends_photo ); ?>');">
            <div class="loft-image-card__overlay">
                <span class="loft-pill">Friends + glam</span>
                <h3 class="loft-hero__title" style="font-size: 22px; margin: 6px 0 0;">Luxury spaces to show off.</h3>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-banner">
            <h3 class="loft-banner__title">Self-serve billing built in.</h3>
            <p class="loft-banner__text">Guests can pay in-app, add extras, and keep everything seamless.</p>
            <div class="loft-footer-actions">
                <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Book now</a>
                <a class="loft-btn loft-btn--ghost" href="<?php echo esc_url( $payment_url ); ?>">Pay my bill</a>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
