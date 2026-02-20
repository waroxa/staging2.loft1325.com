<?php
/**
 * Mobile Footer Template
 *
 * This template renders the consistent mobile footer for the Loft1325 Mobile Experience plugin.
 */

$locale   = get_locale();
$language = strpos( $locale, 'en_' ) === 0 ? 'en' : 'fr';
$strings  = array(
    'sticky_note'            => $language === 'en' ? 'You found the best rate.' : 'Vous avez trouvé le meilleur prix.',
    'finalize_cta'           => $language === 'en' ? 'Finalize' : 'Finaliser',
);

?>
  </main><!-- .main-content -->

  <footer class="footer">
    <div class="footer-inner">
      <p class="sticky-note"><?php echo esc_html( $strings['sticky_note'] ); ?></p>
      <button class="primary-button finalize-button"><?php echo esc_html( $strings['finalize_cta'] ); ?></button>
    </div>
  </footer>

</div><!-- .mobile-shell -->

<?php wp_footer(); ?>
</body>
</html>
