<?php
/**
 * Settings page class file.
 *
 * @package LearnDash\Thrivecart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( class_exists( 'LearnDash_Settings_Page' ) ) :
	/**
	 * Settings page class.
	 *
	 * @since 1.0
	 */
	class LearnDash_Thrivecart_Settings_Page extends LearnDash_Settings_Page {
		public function __construct() {
			$this->parent_menu_page_url  = 'edit.php?post_type=ld-thrivecart';
			$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id      = 'ld-thrivecart-settings';
			$this->settings_page_title   = __( 'LearnDash Thrivecart Settings', 'learndash-thrivecart' );
			$this->settings_tab_title    = __( 'Settings', 'learndash-thrivecart' );
			$this->settings_tab_priority = 9;

			parent::__construct();
		}
	}

	add_action(
		'learndash_settings_pages_init',
		function() {
			LearnDash_Thrivecart_Settings_Page::add_page_instance();
		}
	);

endif;
