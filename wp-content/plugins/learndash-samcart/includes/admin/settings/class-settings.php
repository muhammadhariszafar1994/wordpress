<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit();

/**
* LearnDash_Samcart_Settings class
*
* This class is responsible for managing plugin settings.
*
* @since 0.1
*/
class LearnDash_Samcart_Settings {

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
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'check_learndash_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'learndash_submenu', array( $this, 'submenu_item' ) );
		add_filter( 'learndash_admin_tabs', array( $this, 'admin_tabs' ), 1, 1 );
		add_filter( 'learndash_admin_tabs_on_page', array( $this, 'admin_tabs_on_page' ), 1, 3 );
	}

	/**
	 * Check if LearnDash plugin is active
	 */
	public function check_learndash_plugin() {
		if ( ! is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			//deactivate_plugins( plugin_basename( LEARNDASH_SAMCART_FILE ) );
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Display admin notice when LearnDash plugin is not activated
	 */
	public function admin_notices() {
		echo '<div class="error"><p>' . __( 'LearnDash plugin is required to activate LearnDash Samcart add-on plugin. Please activate it first.', 'learndash-samcart' ) . '</p></div>';
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts() {
		if ( ! is_admin() || ! isset( $_GET['page'] ) || 'learndash-samcart-settings' != $_GET['page'] ) {
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

		// Load our admin JS
		wp_enqueue_script( 'learndash-samcart-admin-script', LEARNDASH_SAMCART_PLUGIN_URL . '/assets/js/learndash-samcart-admin-script.js', array( 'jquery' ), LEARNDASH_SAMCART_VERSION, true );
	}

	/**
	 * Add zapier submenu in Learndash menu
	 * @param  array  $submenu Existing submenu
	 * @return array           New submenu
	 */
	public function submenu_item( $submenu ) {
		$menu = array( array(
	        'name' => __( 'Samcart', 'learndash-samcart' ),
	        'cap'  => 'manage_options', // @TODO Need to confirm this capability on the menu. 
	        'link' => 'edit.php?post_type=ld-samcart-product',
	    ) );

	    array_splice( $submenu, 9, 0, $menu );

	    return $submenu;
	}

	/**
	 * Add admin tabs for settings page
	 * @param  array $tabs Original tabs
	 * @return array       New modified tabs
	 */
	public function admin_tabs( $tabs ) {
		$current_screen = get_current_screen();

		$tabs['ld-samcart-product'] = array(
			'link'      => 'post-new.php?post_type=ld-samcart-product',
			'name'      => __( 'Add New', 'learndash-samcart' ),
			'id'        => 'ld-samcart-product',
			'menu_link' => 'edit.php?post_type=ld-samcart-product',
		);

		$tabs['edit-ld-samcart-product'] = array(
			'link'      => 'edit.php?post_type=ld-samcart-product',
			'name'      => __( 'Samcart Products', 'learndash-samcart' ),
			'id'        => 'edit-ld-samcart-product',
			'menu_link' => 'edit.php?post_type=ld-samcart-product',
		);

		return $tabs;
	}

	/**
	 * Display active tab on settings page
	 * @param  array $admin_tabs_on_page Original active tabs
	 * @param  array $admin_tabs         Available admin tabs
	 * @param  int 	 $current_page_id    ID of current page
	 * @return array                     Currenct active tabs
	 */
	public function admin_tabs_on_page( $admin_tabs_on_page, $admin_tabs, $current_page_id ) {
		
		$tabs = array( 'ld-samcart-product', 'edit-ld-samcart-product' );

		// Add to new tab
		$admin_tabs_on_page['ld-samcart-product'] = $tabs;
		$admin_tabs_on_page['edit-ld-samcart-product'] = $tabs;
			
		return $admin_tabs_on_page;
	}
}

new LearnDash_Samcart_Settings();