<?php
/*
Template Name: Loft 1325 Mobile - Classic
*/

get_header();

$hero_slides = array(
    // Replace these images to update the hero slider.
    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1501117716987-c8e1ecb2101f?auto=format&fit=crop&w=1400&q=80',
    'https://images.unsplash.com/photo-1502920917128-1aa500764ce7?auto=format&fit=crop&w=1400&q=80',
);

$friends_photo = 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80';
$phone_number  = '(833) 320-4474';
$booking_url   = home_url( '/rooms/' );
$payment_url   = home_url( '/checkout/' );
?>

<div class="loft-template loft-template--classic">
    <div class="loft-topbar">
        <div class="loft-topbar__brand">
            Loft 1325
            <span>Self-serve virtual hotel • Val-d’Or</span>
        </div>
        <a class="loft-topbar__cta" href="<?php echo esc_url( $booking_url ); ?>">Shop stays</a>
    </div>

    <div class="loft-phone">
        <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone_number ) ); ?>"><?php echo esc_html( $phone_number ); ?></a>
    </div>

    <div class="loft-section" data-loft-slider data-images='<?php echo wp_json_encode( $hero_slides ); ?>' data-interval="6500">
        <div class="loft-hero">
            <div class="loft-hero__media" style="background-image: url('<?php echo esc_url( $hero_slides[0] ); ?>');"></div>
            <div class="loft-hero__overlay">
                <div class="loft-hero__badge">Loft 1325 Experience</div>
                <h1 class="loft-hero__title">Modern lofts, mobile-first luxury.</h1>
                <p class="loft-hero__subtitle">Touchless access, digital concierge, and instant booking.</p>
                <div class="loft-footer-actions" style="margin-top: 12px;">
                    <a class="loft-btn loft-btn--ghost" href="<?php echo esc_url( $booking_url ); ?>">Voir les lofts</a>
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
            <div class="loft-label">Loft 1325 Suites</div>
            <h2 class="loft-title">Loft 1325 &amp; Residences — Val-d’Or</h2>
            <p class="loft-text">1325 3e Avenue, Val-d’Or, QC • A premium stay with self-serve check-in and instant bill payment.</p>
        </div>

        <div class="loft-card">
            <div class="loft-grid">
                <label>
                    <span class="loft-label">Date d’arrivée</span>
                    <input class="loft-input" type="text" placeholder="Sélectionner" />
                </label>
                <label>
                    <span class="loft-label">Date de départ</span>
                    <input class="loft-input" type="text" placeholder="Sélectionner" />
                </label>
                <label>
                    <span class="loft-label">Lofts</span>
                    <select class="loft-input">
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                    </select>
                </label>
                <label>
                    <span class="loft-label">Invités</span>
                    <select class="loft-input">
                        <option>1</option>
                        <option>2</option>
                        <option>4</option>
                    </select>
                </label>
            </div>
            <div style="margin-top: 16px;">
                <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Rechercher</a>
            </div>
        </div>

        <div class="loft-tablist">
            <a class="active" href="<?php echo esc_url( $booking_url ); ?>">Photos</a>
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Carte</a>
            <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Groupes</a>
            <a href="<?php echo esc_url( home_url( '/weddings/' ) ); ?>">Mariages</a>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-feature">
            <div>✨</div>
            <div>
                <strong>Self-serve luxury</strong>
                <span>Arrive anytime with a mobile key and a concierge in your pocket.</span>
            </div>
        </div>
        <div class="loft-divider"></div>
        <div class="loft-feature">
            <div>💳</div>
            <div>
                <strong>Pay bills instantly</strong>
                <span>Handle invoices and add-ons in seconds, right from your phone.</span>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-image-card" style="background-image: url('<?php echo esc_url( $friends_photo ); ?>');">
            <div class="loft-image-card__overlay">
                <span class="loft-pill">Her &amp; her friends</span>
                <h3 class="loft-hero__title" style="font-size: 22px; margin: 6px 0 0;">Wow-worthy nights &amp; photo-ready spaces.</h3>
            </div>
        </div>
    </div>

    <div class="loft-section">
        <div class="loft-banner">
            <h3 class="loft-banner__title">Your stay, your way.</h3>
            <p class="loft-banner__text">Book, unlock, and pay from your phone. Perfect for high-end, self-serve guests.</p>
            <div class="loft-footer-actions">
                <a class="loft-btn" href="<?php echo esc_url( $booking_url ); ?>">Shop the lofts</a>
                <a class="loft-btn loft-btn--ghost" href="<?php echo esc_url( $payment_url ); ?>">Pay my bill</a>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
