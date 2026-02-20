<?php
/**
 * Mobile Page Template
 *
 * This template is used for all pages and posts on mobile devices (except the front page).
 * It wraps the page content with the mobile header, menu, and applies mobile-specific styling.
 */

get_header( 'mobile' );
?>

<div class="loft-mobile-page-wrapper">
    <div class="loft-mobile-page-content">
        <?php
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'loft-mobile-article' ); ?>>
                    <div class="loft-mobile-article-content">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            }
        } else {
            ?>
            <div class="loft-mobile-no-content">
                <p><?php esc_html_e( 'No content found', 'loft1325-mobile-home' ); ?></p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
get_footer( 'mobile' );
?>
