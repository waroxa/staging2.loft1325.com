<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
use PDFPro\Helper\Functions as Utils;

$id = wp_unique_id('block-');

extract($attributes);

if ($protect) {
    $attributes['file'] = Utils::scramble('encode', $attributes['file']);
}

$className = $className ?? '';
$blockClassName = 'wp-block-pdfp-pdf-poster ' . $className . ' align' . $align;
$isPopupEnabled = isset($popupOptions['enabled']) ? $popupOptions['enabled'] : false;

?>

<div
    class='<?php echo esc_attr($blockClassName); ?>'
    id='<?php echo esc_attr($id); ?>'
    data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>'
    style="text-align: <?php echo esc_attr($alignment) ?>">
    <?php if (!$protect && !$isPopupEnabled) { ?>

        <iframe title="<?php echo esc_attr($attributes['title']); ?>" style="border:0;" width="100%" height="800px" class="pdfp_unsupported_frame" src="//docs.google.com/gview?embedded=true&url=<?php echo esc_url($file) ?>"></iframe>

    <?php } ?>
</div>