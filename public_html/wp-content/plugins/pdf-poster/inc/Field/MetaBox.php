<?php

namespace PDFPro\Field;

use PDFPro\Api\DropboxApi;
use PDFPro\Api\GoogleDriveApi;
use PDFPro\Helper\Functions as Utils;

class MetaBox
{

	private $metabox_prefix = '_fpdf';
	private $option = null;

	public function register()
	{
		add_action('init', array($this, 'register_metabox'), 0);
	}

	public function register_metabox()
	{
		global $pdfp_bs;
		if (class_exists('\CSF')) {
			\CSF::createMetabox($this->metabox_prefix, array(
				'title' => 'PDF Poster Configuration',
				'post_type' => 'pdfposter'
			));

			$this->configure();
		}
	}

	public function configure()
	{
		if (!$this->option) {
			$this->option = get_option('fpdf_option');
		}
		new DropboxApi(Utils::isset($this->option, 'dropbox_app_key'));

		new GoogleDriveApi(Utils::isset($this->option, 'google_apikey'), Utils::isset($this->option, 'google_client_id'), Utils::isset($this->option, 'google_project_number'));

		\CSF::createSection($this->metabox_prefix, array(
			'title'  => '',
			'fields' => array(
				array(
					'id' => 'dropbox_button',
					'class' => 'bplugins-meta-readonly',
					'type' => 'content',
					'content' => '<div id="picker_container"></div>'
				),
				array(
					'id'    => 'source',
					'type'  => 'upload',
					'title' => __('Add PDF source', 'pdfp'),
					'attributes' => array('id' => 'picker_field')
				),
				array(
					'id' => 'height',
					'title' => 'Height',
					'type' => 'dimensions',
					'width' => false,
					'default' => $this->pdfp_preset('preset_height', [
						'height' => 842,
						'unit' => 'px'
					])
				),
				array(
					'id' => 'width',
					'title' => 'Width',
					'type' => 'dimensions',
					'height' => false,
					'default' => $this->pdfp_preset('preset_width', [
						'width' => '100',
						'unit' => '%'
					])
				),
				array(
					'id' => 'print',
					'title' => __('Allow Print', 'pdfp'),
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_print'),
					'desc' => __('Check if you allow visitor to print the pdf file .', 'pdfp')
				),
				array(
					'id' => 'show_filename',
					'title' => __('Show file name on top', 'pdfp'),
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_show_filename', true),
					'desc' => __('Check if you want to show the file name in the top of the viewer.', 'pdfp')
				),
				array(
					'id' => 'view_fullscreen_btn',
					'title' => __('Show view fullscreen button on top', 'pdfp'),
					'class' => 'bplugins-meta-readonly',
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_view_fullscreen_btn', true),
					'desc' => __('Check if you want to show "View Full Screen" Button in the top of the viewer.', 'pdfp')
				),
				array(
					'id' => 'fullscreen_btn_text',
					'title' => __('Fullscreen Button Text', 'pdfp'),
					'type' => 'text',
					'default' => $this->pdfp_preset('preset_fullscreen_btn_text', 'View Fullscreen'),
					'dependency' => array('view_fullscreen_btn', '==', '1')
				),
				array(
					'id' => 'show_download_btn',
					'title' => __('Show download button on top', 'pdfp'),
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_show_download_btn', true),
					'desc' => __('Check if you want to show "Download" Button in the top of the viewer.', 'pdfp')
				),
				array(
					'id' => 'only_pdf',
					'title' => __('Show Only PDF', 'pdfp'),
					'class' => 'bplugins-meta-readonly',
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_only_pdf'),
					'desc' => __('Enable if you want to hide black background and PDF menu', 'pdfp')
				),

				array(
					'id' => 'default_browser',
					'title' => __('Enable Google Doc Viewer', 'pdfp'),
					'class' => 'bplugins-meta-readonly',
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_default_browser'),
					'desc' => '<span style="color:red">Sometimes Microsoft Edge block pdf due to security reason. <b>Enable Download button, download the PDF and upload again.</b> or Check if you want to use Google doc Viewer to solve the problem. <b>Note: protection will not work if you check this option</b>'
				),

				array(
					'id' => 'view_fullscreen_btn_target_blank',
					'title' => 'Open in new window',
					'class' => 'bplugins-meta-readonly',
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_view_fullscreen_btn_target_blank', false),
				),
				array(
					'id' => 'protect',
					'title' => __('Protect my content', 'pdfp'),
					'class' => 'bplugins-meta-readonly',
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_protect', 0),
					'desc' => __('Check to disable Mouse clicks to protect your content.', 'pdfp'),
				),
				array(
					'id' => 'disable_alert',
					'title' => __('Disable Alert Message', 'pdfp'),
					'type' => 'switcher',
					'class' => 'bplugins-meta-readonly',
					'default' => $this->pdfp_preset('preset_disable_alert', true),
					'desc' => __('Check to disable alert message.', 'pdfp'),
					'dependency' => array('protect', '==', '1')
				),
				array(
					'id' => 'thumbnail_toggle_menu',
					'title' => __('Thumbnails toggle menu', 'pdfp'),
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_thumbnail_toggle_menu', false),
					'class' => 'bplugins-meta-readonly',
					'desc' => __('Enable to enable Thumbnails Toogle Menu in the viewer', 'pdfp'),
				),
				array(
					'id' => 'sidebar_open',
					'title' => __('Thumbnails open by default', 'pdfp'),
					'type' => 'switcher',
					'default' => $this->pdfp_preset('preset_sidebar_open', false),
					'class' => 'bplugins-meta-readonly',
					'desc' => __('Enable to enable Thumbnails Toogle Menu in the viewer', 'pdfp'),
				),
				array(
					'id' => 'ppv_load_last_version',
					'title' => __('Load the last version of the pdf', 'pdfp'),
					'type' => 'switcher',
					'class' => 'bplugins-meta-readonly',
					'default' => $this->pdfp_preset('preset_ppv_load_last_version', false),
					'desc' => __('Enable to Load the last version of the pdf', 'pdfp')
				),
				array(
					'id' => 'hr_scroll',
					'title' => __('Horizontal Scrollbar', 'pdfp'),
					'type' => 'switcher',
					'class' => 'bplugins-meta-readonly',
					'default' => $this->pdfp_preset('preset_hr_scroll', false),
					'desc' => __('Set Horizontal scrollbar as default', 'pdfp')
				),
				array(
					'id' => 'jump_to',
					'title' => __('Jump To Page', 'pdfp'),
					'type' => 'number',
					'class' => 'bplugins-meta-readonly',
					'desc' => __('Enter the page number that will be shown in the viewer', 'pdfp'),
					'default' => 1
				),
				array(
					'id' => 'zoomLevel',
					'title' => __('Zoom Level', 'pdfp'),
					'type' => 'number',
					'class' => 'bplugins-meta-readonly',
					'desc' => __('Enter the zoom level. leave empty to set auto', 'pdfp'),
					'default' => '',
					'unit' => '%'
				),
				array(
					'id'      => 'readonly',
					'title'   => __('Enable Popup', 'pdfp'),
					'type'    => 'switcher',
					'class' => 'bplugins-meta-readonly',
					'desc'    => __('Enable or disable the popup functionality.', 'pdfp'),
					'default' => false,
				),
			)
		));
	}


	public function pipeError($prefix)
	{
		\CSF::createSection($prefix, array(
			'title' => '',
			'fields' => array(
				array(
					'type' => 'heading',
					'content' => '<p style="color:#7B2F31;background:#F8D7DA;padding:15px">PDF Poster PRO is not activated yet. Please active the license key by navigating to Plugins> PDF Poster PRO > Active License. 
					Once you active the plugin you will get all the options availble here. </p>'
				),
			),
		));
	}

	function pdfp_preset($key, $default = false)
	{
		$settings = get_option('fpdf_option');
		return $settings[$key] ?? $default;
	}
}
