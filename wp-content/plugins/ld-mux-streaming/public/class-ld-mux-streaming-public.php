<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LD_Mux_Streaming
 * @subpackage LD_Mux_Streaming/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    LD_Mux_Streaming
 * @subpackage LD_Mux_Streaming/public
 * @author     Your Name <email@example.com>
 */
class LD_Mux_Streaming_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ld_mux_streaming    The ID of this plugin.
	 */
	private $ld_mux_streaming;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $ld_mux_streaming       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $ld_mux_streaming, $version ) {

		$this->ld_mux_streaming = $ld_mux_streaming;
		$this->version = $version;

		add_shortcode('mux_streaming_react_app', array($this, 'render_react_app'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->ld_mux_streaming, plugin_dir_url( __FILE__ ) . 'css/ld-mux-streaming-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	}

	// public function add_sfwd_plant_activity_post_type() {
	// 	$args = array(
	// 		'sfwd-plant-activity2' => array(
	// 			'name'        => 'sfwd-plant-activity2',
	// 			'post_label'  => esc_html__( 'Plant Activity', 'ld-plant-activity2' ),
	// 			'cpt_options' => array(
	// 				'labels' => array(
	// 					'name'               => esc_html__( 'Plant Activities', 'ld-plant-activity2' ),
	// 					'singular_name'      => esc_html__( 'Plant Activity', 'ld-plant-activity2' ),
	// 					'add_new'            => esc_html__( 'Add New', 'ld-plant-activity2' ),
	// 					'add_new_item'       => esc_html__( 'Add New Plant Activity', 'ld-plant-activity2' ),
	// 					'edit_item'          => esc_html__( 'Edit Plant Activity', 'ld-plant-activity2' ),
	// 					'new_item'           => esc_html__( 'New Plant Activity', 'ld-plant-activity2' ),
	// 					'view_item'          => esc_html__( 'View Plant Activity', 'ld-plant-activity2' ),
	// 					'search_items'       => esc_html__( 'Search Plant Activities', 'ld-plant-activity2' ),
	// 					'not_found'          => esc_html__( 'No Plant Activities found.', 'ld-plant-activity2' ),
	// 					'not_found_in_trash' => esc_html__( 'No Plant Activities found in Trash.', 'ld-plant-activity2' ),
	// 				),
	// 				'public'              => true,
	// 				'show_ui'             => true,
	// 				'show_in_nav_menus'   => true,
	// 				'show_in_menu'        => 'learndash-lms',
	// 				'show_in_rest'        => true,
	// 				'has_archive'         => false,
	// 				'rewrite'             => array( 'slug' => 'plant-activity2' ),
	// 				'supports'            => array( 'title', 'editor', 'thumbnail' ),
	// 				'menu_position'       => 35,
	// 				'menu_icon'           => 'dashicons-admin-site-alt',
	// 				'map_meta_cap' => true,
	// 				'capabilities'    => array(
	// 					'create_posts' => false,
	// 				),
	// 			),
	// 		),
	// 	);

	// 	foreach ( $args as $post_type => $data ) {
	// 		register_post_type( $post_type, $data['cpt_options'] );
	// 	}
	// }

}