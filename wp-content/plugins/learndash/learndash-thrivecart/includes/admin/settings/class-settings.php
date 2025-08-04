<?php
/**
 * Settings class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Thrivecart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * LearnDash_Thrivecart_Settings class
 *
 * This class is responsible for managing plugin settings.
 *
 * @since 0.1
 */
class LearnDash_Thrivecart_Settings {

	/**
	 * Plugin options
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $options;

	/**
	 * Class __construct function
	 *
	 * @since 0.1
	 */
	public function __construct() {
		 add_action( 'admin_init', array( $this, 'check_learndash_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'learndash_submenu', array( $this, 'submenu_item' ) );
		add_filter( 'learndash_admin_tabs_set', array( $this, 'admin_tabs_set' ), 10, 2 );
	}

	/**
	 * Check if LearnDash plugin is active.
	 */
	public function check_learndash_plugin() {
		if ( ! is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Display admin notice when LearnDash plugin is not activated.
	 */
	public function admin_notices() {
		echo '<div class="error"><p>' . __( 'LearnDash plugin is required to activate LearnDash Thrivecart add-on plugin. Please activate it first.', 'learndash-thrivecart' ) . '</p></div>';
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts() {
		if ( ! is_admin() || ! isset( $_GET['page'] ) || ( isset( $_GET['page'] ) && 'learndash-thrivecart-settings' != $_GET['page'] ) ) {
			return;
		}

		// we need to load the LD plugin style.css, sfwd_module.css and sfwd_module.js because we want to replicate the styling on the admin tab.
		wp_enqueue_style( 'learndash_style', plugins_url() . '/sfwd-lms/assets/css/style.css' );
		wp_enqueue_style( 'sfwd-module-style', plugins_url() . '/sfwd-lms/assets/css/sfwd_module.css' );
		wp_enqueue_script( 'sfwd-module-script', plugins_url() . '/sfwd-lms/assets/js/sfwd_module.js', array( 'jquery' ), LEARNDASH_VERSION, true );

		// Need this because sfwd_module.js expects a json data array be passed.
		$data = array();
		$data = array( 'json' => json_encode( $data ) );
		wp_localize_script( 'sfwd-module-script', 'sfwd_data', $data );
	}

	/**
	 * Add tabs to achievement pages
	 *
	 * @param  string $current_screen_parent_file Current screen parent
	 * @param  object $tabs                       Learndash_Admin_Menus_Tabs object
	 */
	public function admin_tabs_set( $current_screen_parent_file, $tabs ) {
		$screen = get_current_screen();

		if ( $current_screen_parent_file == 'learndash-lms' && $screen->post_type == 'ld-thrivecart' ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-thrivecart',
					'name' => __( 'Add New', 'learndash-thrivecart' ),
					'id'   => 'ld-thrivecart',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-thrivecart',
					'name' => __( 'LearnDash Thrivecart', 'learndash-thrivecart' ),
					'id'   => 'edit-ld-thrivecart',
				),
				2
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'admin.php?page=ld-thrivecart-settings',
					'name' => __( 'Settings', 'learndash-thrivecart' ),
					'id'   => 'ld-thrivecart-settings',
				),
				3
			);

		} elseif ( $current_screen_parent_file == 'edit.php?post_type=ld-thrivecart' ) {
			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'post-new.php?post_type=ld-thrivecart',
					'name' => __( 'Add New', 'learndash-thrivecart' ),
					'id'   => 'ld-thrivecart',
				),
				1
			);

			$tabs->add_admin_tab_item(
				$current_screen_parent_file,
				array(
					'link' => 'edit.php?post_type=ld-thrivecart',
					'name' => __( 'LearnDash Thrivecart', 'learndash-thrivecart' ),
					'id'   => 'edit-ld-thrivecart',
				),
				2
			);
		}
	}

	/**
	 * Add zapier submenu in Learndash menu.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $submenu Existing submenu.
	 *
	 * @return array New submenu
	 */
	public function submenu_item( $submenu ) {
		$menu = array(
			array(
				'name' => __( 'Thrivecart', 'learndash-thrivecart' ),
				'cap'  => 'manage_options', // @TODO Need to confirm this capability on the menu.
				'link' => 'edit.php?post_type=ld-thrivecart',
			),
		);

		array_splice( $submenu, 9, 0, $menu );

		return $submenu;
	}
}

new LearnDash_Thrivecart_Settings();
