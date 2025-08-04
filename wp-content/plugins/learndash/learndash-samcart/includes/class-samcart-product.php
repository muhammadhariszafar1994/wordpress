<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
* Samcart Product class
*/
class LearnDash_Samcart_Product
{
	
	public function __construct()
	{
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 3 );
	}

	/**
	* Registers samcart product post type
	* @uses $wp_post_types Inserts new post type object into the list
	*
	* @param string  Post type key, must not exceed 20 characters
	* @param array|string  See optional args description above.
	* @return object|WP_Error the registered post type object, or an error object
	*/
	public function register_post_type() {
	
		$labels = array(
			'name'                => __( 'Samcart Products', 'learndash-samcart' ),
			'singular_name'       => __( 'Samcart Product', 'learndash-samcart' ),
			'add_new'             => _x( 'Add New Samcart Product', 'learndash-samcart', 'learndash-samcart' ),
			'add_new_item'        => __( 'Add New Samcart Product', 'learndash-samcart' ),
			'edit_item'           => __( 'Edit Samcart Product', 'learndash-samcart' ),
			'new_item'            => __( 'New Samcart Product', 'learndash-samcart' ),
			'view_item'           => __( 'View Samcart Product', 'learndash-samcart' ),
			'search_items'        => __( 'Search Samcart Products', 'learndash-samcart' ),
			'not_found'           => __( 'No Samcart Products found', 'learndash-samcart' ),
			'not_found_in_trash'  => __( 'No Samcart Products found in Trash', 'learndash-samcart' ),
			'parent_item_colon'   => __( 'Parent Samcart Product:', 'learndash-samcart' ),
			'menu_name'           => __( 'Samcart Products', 'learndash-samcart' ),
		);
	
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => __( 'Samcart products associated with a Samcart account.', 'learndash-samcart' ),
			'taxonomies'          => array(),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => null,
			'menu_icon'           => null,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array(
				'title',
				)
		);
	
		register_post_type( 'ld-samcart-product', $args );
	}

	public function add_meta_boxes()
	{
		add_meta_box( 'learndash-samcart-product', __( 'Settings', 'learndash-samcart' ), array( $this, 'output_meta_box' ), 'ld-samcart-product', 'normal' );
	}

	public function save_meta_boxes( $post_id, $post, $update )
	{
		// exit if post type is not ld-samcart-product
		$post_type = get_post_type( $post_id );

		if ( $post_type != 'ld-samcart-product' ) {
			return;
		};

		if ( wp_verify_nonce( 'ld_samcart_nonce', 'save_meta_boxes' ) ) {
			wp_die( __( 'Cheatin\' huh?', 'learndash-samcart' ) );
		}

		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'ld_samcart' ) === false ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = array_map( 'sanitize_text_field', $value );
			} else {
				$value = trim( $value );
				$value = sanitize_text_field( $value );
			}

			update_post_meta( $post_id, $key, $value );
		}

		if ( ! isset( $_POST['_ld_samcart_courses' ] ) ) {
			update_post_meta( $post_id, '_ld_samcart_courses', array() );
		}
	}

	public function output_meta_box()
	{
		$settings = $this->get_meta_settings();

		ob_start();
		?>

		<?php wp_nonce_field( 'save_meta_boxes', 'ld_samcart_nonce' ); ?>

		<div class="sfwd sfwd_options sfwd-courses_settings">
			<?php foreach ( $settings as $id => $setting ) : ?>
			<div class="sfwd_input" id="learndash-samcart-courses">
				<span class="sfwd_option_label">
					<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php _e( 'Click for Help!', 'learndash' ); ?>" onclick="toggleVisibility('<?php echo $id; ?>_tip');">
						<img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
						<label class="sfwd_label">
							<?php echo esc_attr( $setting['label'] ); ?>
						</label>
					</a>
				</span>
				<span class="sfwd_option_input">
					<div class="sfwd_option_div">
						<?php $output = 'output_' . $id; ?>
						<?php $this->$output() ?>
					</div>
					<div class="sfwd_help_text_div">
						<label class="sfwd_help_text" style="display: none;" id="<?php echo esc_attr( $id ); ?>_tip">
							<?php echo esc_attr( $setting['desc'] ); ?>
						</label>
					</div>
				</span>
				<p style="clear: left;"></p>
			</div>
			<?php endforeach; ?>
		</div>

		<?php
		echo ob_get_clean();
	}

	public function get_meta_settings()
	{
		$settings = array(
			'product_id' => array(
				'label' => __( 'Samcart Product ID', 'learndash-samcart' ),
				'desc' => __( 'Unique Samcart product ID that you have created in your Samcart account.', 'learndash-samcart' ),
			),
			'courses' => array(
				'label' => __( 'Associated Courses', 'learndash-samcart' ),
				'desc'  => __( 'Course(s) you want to associate with Samcart product. Press ctrl to select multiple courses.', 'learndash-samcart' ),
			),
			'notification_url' => array(
				'label' => __( 'Notification URL', 'learndash-samcart' ),
				'desc' => __( 'URL that you need to paste into your Samcart product notification URL.', 'learndash-samcart' )
			),
		);

		return apply_filters( 'learndash_samcart_product_meta_settings', $settings );
	}

	public function output_product_id()
	{
		$product_id = get_post_meta( get_the_ID(), '_ld_samcart_product_id', true );
		?>

		<input type="text" name="_ld_samcart_product_id" value="<?php echo esc_attr( $product_id ); ?>">

		<?php
	}

	public function output_courses()
	{
		$courses      = $this->get_learndash_courses();
		$courses_meta = (array) get_post_meta( get_the_ID(), '_ld_samcart_courses', true );
		?>

		<select name="_ld_samcart_courses[]" id="_ld_samcart_courses" multiple="multiple">
			<?php foreach ( $courses as $course_id => $course_title ) : ?>
			<?php $selected = in_array( $course_id, $courses_meta ) ? 'selected="selected"' : ''; ?>
			<option value="<?php echo esc_attr( $course_id ); ?>" <?php echo esc_attr( $selected ); ?>>
				<?php echo esc_attr( $course_title ); ?>
			</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	public function output_notification_url()
	{
		$url = add_query_arg( array(
			'learndash-integration' => 'samcart',
		), home_url( '/' ) );

		?>

		<input type="text" readonly="readonly" value="<?php echo esc_attr( $url ); ?>">

		<?php
	}

	public function get_learndash_courses()
	{
		global $post;
		$postid = $post->ID;

		query_posts( array( 'post_type' => 'sfwd-courses', 'posts_per_page' => - 1 ) );

		$courses = array();
		while ( have_posts() ) {
			the_post();
			$courses[ get_the_ID() ] = get_the_title();
		}
		wp_reset_query();

		$post = get_post( $postid );

		return $courses;
	}
}

new LearnDash_Samcart_Product();