<?php

if (!class_exists('PDFPAdmin')) {
	class PDFPAdmin
	{
		function __construct()
		{
			add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
			add_action('admin_menu', [$this, 'adminMenu']);
		}

		function adminEnqueueScripts($hook)
		{
			if (str_contains($hook, 'pdf-poster')) {
				wp_enqueue_style('pdfp-dashboard-style', PDFPRO_PLUGIN_DIR . 'build/dashboard.css', [], PDFPRO_VER);

				wp_enqueue_script('pdfp-dashboard-script', PDFPRO_PLUGIN_DIR . 'build/dashboard.js', ['react', 'react-dom',  'wp-components', 'wp-i18n', 'wp-api', 'wp-util', 'lodash', 'wp-media-utils', 'wp-data', 'wp-core-data', 'wp-api-request'], PDFPRO_VER, true);
				wp_localize_script('pdfp-dashboard-script', 'pdfpDashboard', [
					'dir' => PDFPRO_PLUGIN_DIR,
				]);
			}
		}

		function adminMenu()
		{
			add_menu_page(
				__('PDF Poster', 'pdfp'),
				__('PDF Poster', 'pdfp'),
				'edit_others_posts',
				'pdf-poster',
				[$this, 'dashboardPage'],
				PDFPRO_PLUGIN_DIR . '/img/icn.png',
				15
			);

			add_submenu_page(
				'pdf-poster',
				__('Add New', 'pdfp'),
				__(' &#8627; Add New', 'pdfp'),
				'manage_options',
				'pdf-poster-add-new',
				[$this, 'redirectToAddNew'],
				2
			);

			add_submenu_page(
				'pdf-poster',
				__('Dashboard', 'pdfp'),
				__('Dashboard', 'pdfp'),
				'edit_others_posts',
				'pdf-poster',
				[$this, 'dashboardPage'],
				0
			);
		}

		function dashboardPage()
		{ ?>
			<div id='pdfpAdminDashboard' data-info='<?php echo esc_attr(wp_json_encode([
														'version' => PDFPRO_VER,
														'isPremium' => pdfp_fs()->can_use_premium_code(),
														'hasPro' => true
													])); ?>'></div>
		<?php }

		function upgradePage()
		{ ?>
			<div id='pdfpAdminUpgrade' data-info='<?php echo esc_attr(wp_json_encode([
														'version' => PDFPRO_VER,
														'isPremium' => pdfp_fs()->can_use_premium_code(),
														'hasPro' => true
													])); ?>'>Coming soon...</div>
			<?php }

		/**	
		 * Redirect to add new Model Viewer
		 * */
		function redirectToAddNew()
		{
			if (function_exists('headers_sent') && headers_sent()) {
			?>
				<script>
					window.location.href = "<?php echo esc_url(admin_url('post-new.php?post_type=pdfposter')); ?>";
				</script>
<?php
			} else {
				wp_redirect(admin_url('post-new.php?post_type=pdfposter'));
			}
		}
	}
	new PDFPAdmin;
}
