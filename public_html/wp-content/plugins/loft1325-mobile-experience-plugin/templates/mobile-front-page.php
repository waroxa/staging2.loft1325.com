<?php
/**
 * Mobile Front Page Template
 * 
 * This template renders the mobile-first homepage experience.
 */

get_template_part( 'templates/mobile-header' );
?>

<div class="mobile-content">
    <h1><?php esc_html_e( 'SÉLECTIONNER UNE CHAMBRE', 'loft1325-mobile' ); ?></h1>
    <p class="subtitle"><?php esc_html_e( 'EXPÉRIENCE HÔTELIÈRE 100 % VIRTUELLE', 'loft1325-mobile' ); ?></p>
    
    <p><?php esc_html_e( 'Le confort d\'une maison avec l\'expérience hôtelière, gérez tout depuis votre mobile.', 'loft1325-mobile' ); ?></p>
    
    <!-- Booking Summary -->
    <div class="booking-summary">
        <span class="label"><?php esc_html_e( 'DATES', 'loft1325-mobile' ); ?></span>
        <span class="value"><?php esc_html_e( 'Sélectionnez vos dates', 'loft1325-mobile' ); ?> 📅</span>
    </div>
    
    <div class="booking-summary">
        <span class="label"><?php esc_html_e( 'VOYAGEURS', 'loft1325-mobile' ); ?></span>
        <span class="value"><?php esc_html_e( '2 adultes · 0 enfant', 'loft1325-mobile' ); ?> 👤</span>
    </div>
    
    <!-- Rooms Listing -->
    <div class="rooms-container">
        <?php
        $args = array(
            'post_type'      => 'post', // Adjust based on your custom post type for lofts
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        
        $rooms = new WP_Query( $args );
        
        if ( $rooms->have_posts() ) {
            while ( $rooms->have_posts() ) {
                $rooms->the_post();
                ?>
                <div class="room-card">
                    <?php
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'large', array( 'class' => 'room-image' ) );
                    }
                    ?>
                    <h3><?php the_title(); ?></h3>
                    <p class="pricing"><?php esc_html_e( 'À partir de ', 'loft1325-mobile' ); ?><?php echo esc_html( get_post_meta( get_the_ID(), 'price', true ) ); ?> CAD · <?php esc_html_e( 'par nuit', 'loft1325-mobile' ); ?></p>
                    <p class="daily-rate"><?php esc_html_e( 'Tarif du jour: ', 'loft1325-mobile' ); ?><strong><?php echo esc_html( get_post_meta( get_the_ID(), 'daily_price', true ) ); ?> CAD</strong></p>
                    <a href="<?php the_permalink(); ?>" class="btn"><?php esc_html_e( 'RÉSERVER MAINTENANT', 'loft1325-mobile' ); ?></a>
                </div>
                <?php
            }
            wp_reset_postdata();
        }
        ?>
    </div>
    
    <!-- Restaurants Section -->
    <div class="restaurants-section">
        <h2><?php esc_html_e( 'RESTAURANTS', 'loft1325-mobile' ); ?></h2>
        <div class="restaurants-grid">
            <?php
            $restaurants = get_option( 'loft1325_restaurants', array() );
            if ( ! empty( $restaurants ) ) {
                foreach ( $restaurants as $restaurant ) {
                    ?>
                    <div class="restaurant-item">
                        <?php
                        if ( ! empty( $restaurant['logo'] ) ) {
                            ?>
                            <img src="<?php echo esc_url( $restaurant['logo'] ); ?>" alt="<?php echo esc_attr( $restaurant['name'] ); ?>" class="restaurant-logo">
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>

<?php
get_template_part( 'templates/mobile-menu' );
?>
