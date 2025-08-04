<?php
namespace LearnDash\Integrity\Admin;

use LearnDash_Settings_Page;

/**
 * LearnDash Settings Page for Integrity addon.
 *
 * @package LearnDash
 * @subpackage Settings
 */

/**
 * Class to create the settings page.
 */
class Settings_Page extends LearnDash_Settings_Page {

	/**
	 * Public constructor for class
	 */
	public function __construct() {
		$this->parent_menu_page_url  = 'admin.php?page=learndash_lms_settings';
		$this->menu_page_capability  = LEARNDASH_ADMIN_CAPABILITY_CHECK;
		$this->settings_page_id      = 'learndash_lms_settings_integrity';
		$this->settings_page_title   = esc_html__( 'Integrity Settings', 'learndash-integrity' );
		$this->settings_tab_title    = esc_html__( 'Integrity', 'learndash-integrity' );
		$this->settings_tab_priority = 40;

		parent::__construct();
	}
}

add_action( 'learndash_settings_pages_init', function() {
	Settings_Page::add_page_instance();
} );
