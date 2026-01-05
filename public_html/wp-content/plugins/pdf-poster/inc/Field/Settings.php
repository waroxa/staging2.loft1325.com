<?php

namespace PDFPro\Field;

class Settings
{

	private $option_prefix = 'fpdf_option';
	public function register()
	{
		add_action('init', array($this, 'init'), 0);
	}

	public function init()
	{
		if (class_exists('\CSF')) {
			\CSF::createOptions($this->option_prefix, array(
				'framework_title' => __('PDF Poster Settings', 'pdfp'),
				'menu_title'  => __('Settings', 'pdfp'),
				'menu_slug'   => 'fpdf-settings',
				'menu_type'   => 'submenu',
				'menu_parent' => 'pdf-poster',
				'theme' => 'light',
				'show_bar_menu' => false,
				'footer_text' => 'Thank you for using PDF Poster',
			));

			$this->shortcode();
		}
	}

	public function shortcode()
	{
		\CSF::createSection($this->option_prefix, array(
			'title' => 'Shortcode',
			'fields' => array(
				array(
					'id' => 'pdfp_gutenberg_enable',
					'type' => 'switcher',
					'title' => __('Enable Gutenberg shortcode generator', 'pdfp'),
					'default' => get_option('pdfp_gutenberg_enable', false)
				)
			)
		));
	}

	function pdfp_preset($key, $default = false)
	{
		$settings = get_option('fpdf_option');
		return $settings[$key] ?? $default;
	}
}
