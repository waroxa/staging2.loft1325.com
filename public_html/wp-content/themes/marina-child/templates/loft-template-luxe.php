<?php
/*
Template Name: Loft 1325 Mobile - Luxe
*/

get_header();

$hero_slides = array(
    // Replace these images to update the hero slider.
    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
);

$friends_photo = 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80';
$booking_url   = home_url( '/rooms/' );
$payment_url   = home_url( '/checkout/' );
?>

<div class="loft-template loft-template--luxe">
    <div class="loft-topbar">
        <div class="loft-topbar__brand">
            Loft 1325 Luxe
            <span>VIP-ready virtual hotel</span>
        </div>
        <a class="loft-topbar__cta" href="<?php echo esc_url( $booking_url ); ?>">Reserve</a>
    </div>

    <div class="loft-section" data-loft-slider data-images='<?php echo wp_json_encode( $hero_slides ); ?>' data-interval="5500">
        <div class="loft-hero">
            <div class="loft-hero__media" style="background-image: url('<?php echo esc_url( $hero_slides[0] ); ?>');"></div>
            <div class="loft-hero__overlay">
                <div class="loft-hero__badge">Signature Collection</div>
                <h1 class="loft-hero__title">The wow factor starts before check-in.</h1>
                <p class="loft-hero__subtitle">Effortless luxury suites + instant billing.</p>
                <div class="loft-footer-actions" style="margin-top: 16px;">
                    <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Shop luxury stays</a>
                </div>
            </div>
            <div class="loft-hero__nav">
                <button class="loft-hero__button" type="button" data-loft-prev aria-label="Previous slide">‹</button>
                <button class="loft-hero__button" type="button" data-loft-next aria-label="Next slide">›</button>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-stats">
            <div class="loft-stat"><strong>4.9</strong>Elite reviews</div>
            <div class="loft-stat"><strong>24/7</strong>Digital concierge</div>
            <div class="loft-stat"><strong>2 min</strong>Mobile check-in</div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-card">
            <div class="loft-label">Signature perks</div>
            <h2 class="loft-title">Curated for her + her friends</h2>
            <p class="loft-text">Mood lighting, glam-ready mirrors, and a seamless way to split or pay the bill.</p>
            <div class="loft-divider"></div>
            <div class="loft-grid">
                <div class="loft-feature">
                    <div>🗝️</div>
                    <div>
                        <strong>Instant mobile key</strong>
                        <span>No front desk, just walk in.</span>
                    </div>
                </div>
                <div class="loft-feature">
                    <div>💳</div>
                    <div>
                        <strong>Pay in seconds</strong>
                        <span>Settle everything from your phone.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-image-card" style="background-image: url('<?php echo esc_url( $friends_photo ); ?>');">
            <div class="loft-image-card__overlay">
                <span class="loft-pill">The girls are here</span>
                <h3 class="loft-hero__title" style="font-size: 22px; margin: 6px 0 0;">Photo-ready nights, no waiting.</h3>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-banner">
            <h3 class="loft-banner__title">One tap to book. One tap to pay.</h3>
            <p class="loft-banner__text">Keep it simple, stunning, and premium from start to finish.</p>
            <div class="loft-footer-actions">
                <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">See all lofts</a>
                <a class="loft-btn loft-btn--ghost" href="<?php echo esc_url( $payment_url ); ?>">Pay my bill</a>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
