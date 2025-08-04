<?php
/**
 * LearnDash Thrivecart product class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Thrivecart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Thrivecart Product class.
 *
 * @since 1.0.0
 */
class LearnDash_Thrivecart_Product {
	/**
	 * Constructor.
	 */
	public function __construct() {
		 add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 3 );
	}

	/**
	 * Registers thrivecart product post type.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Thrivecart Products', 'learndash-thrivecart' ),
			'singular_name'      => __( 'Thrivecart Product', 'learndash-thrivecart' ),
			'add_new'            => _x( 'Add New Thrivecart Product', 'learndash-thrivecart', 'learndash-thrivecart' ),
			'add_new_item'       => __( 'Add New Thrivecart Product', 'learndash-thrivecart' ),
			'edit_item'          => __( 'Edit Thrivecart Product', 'learndash-thrivecart' ),
			'new_item'           => __( 'New Thrivecart Product', 'learndash-thrivecart' ),
			'view_item'          => __( 'View Thrivecart Product', 'learndash-thrivecart' ),
			'search_items'       => __( 'Search Thrivecart Products', 'learndash-thrivecart' ),
			'not_found'          => __( 'No Thrivecart Products found', 'learndash-thrivecart' ),
			'not_found_in_trash' => __( 'No Thrivecart Products found in Trash', 'learndash-thrivecart' ),
			'parent_item_colon'  => __( 'Parent Thrivecart Product:', 'learndash-thrivecart' ),
			'menu_name'          => __( 'Thrivecart Products', 'learndash-thrivecart' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => __( 'Thrivecart products associated with a Thrivecart account.', 'learndash-thrivecart' ),
			'taxonomies'          => [],
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
			),
		);

		register_post_type( 'ld-thrivecart', $args );
	}

	/**
	 * Add meta box.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'learndash-thrivecart-product', __( 'Settings', 'learndash-thrivecart' ), array( $this, 'output_meta_box' ), 'ld-thrivecart', 'normal' );
	}

	/**
	 * Save meta box form.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether update or new post.
	 *
	 * @return void
	 */
	public function save_meta_boxes( $post_id, $post, $update ) {
		// exit if post type is not ld-thrivecart
		$post_type = get_post_type( $post_id );

		if ( $post_type != 'ld-thrivecart' ) {
			return;
		};

		if ( wp_verify_nonce( 'ld_thrivecart_nonce', 'save_meta_boxes' ) ) {
			wp_die( __( 'Cheatin\' huh?', 'learndash-thrivecart' ) );
		}

		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'ld_thrivecart' ) === false ) {
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

		if ( ! isset( $_POST['_ld_thrivecart_courses'] ) ) {
			update_post_meta( $post_id, '_ld_thrivecart_courses', [] );
		}

		if ( ! isset( $_POST['_ld_thrivecart_groups'] ) ) {
			update_post_meta( $post_id, '_ld_thrivecart_groups', [] );
		}
	}

	/**
	 * Output meta box.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_meta_box() {
		 $settings = $this->get_meta_settings();

		ob_start();
		?>

		<?php wp_nonce_field( 'save_meta_boxes', 'ld_thrivecart_nonce' ); ?>

		<div class="sfwd sfwd_options sfwd-courses_settings">
			<?php foreach ( $settings as $id => $setting ) : ?>
			<div class="sfwd_input" id="learndash-thrivecart-courses">
				<span class="sfwd_option_label">
					<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php _e( 'Click for Help!', 'learndash' ); ?>" onclick="toggleVisibility('<?php echo $id; ?>_tip');">
						<img src="<?php echo esc_attr( LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ); ?>">
						<label class="sfwd_label">
							<?php echo esc_attr( $setting['label'] ); ?>
						</label>
					</a>
				</span>
				<span class="sfwd_option_input">
					<div class="sfwd_option_div">
						<?php $output = 'output_' . $id; ?>
						<?php $this->$output(); ?>
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

	/**
	 * Get meta settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, string>>
	 */
	public function get_meta_settings() {
		$settings = array(
			'product_id' => array(
				'label' => __( 'Thrivecart Product ID', 'learndash-thrivecart' ),
				'desc'  => __( 'Unique Thrivecart product ID that you have created in your Thrivecart account.', 'learndash-thrivecart' ),
			),
			'courses'    => array(
				'label' => __( 'Associated Courses', 'learndash-thrivecart' ),
				'desc'  => __( 'Course(s) you want to associate with Thrivecart product. Press ctrl on Windows or cmd on Mac to select or deselect multiple courses.', 'learndash-thrivecart' ),
			),
			'groups'     => array(
				'label' => __( 'Associated Groups', 'learndash-thrivecart' ),
				'desc'  => __( 'Group(s) you want to associate with Thrivecart product. Press ctrl on Windows or cmd on Mac to select or deselect multiple groups.', 'learndash-thrivecart' ),
			),
		);

		return apply_filters( 'learndash_thrivecart_product_meta_settings', $settings );
	}

	/**
	 * Output product ID field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_product_id() {
		$product_id = get_post_meta( get_the_ID(), '_ld_thrivecart_product_id', true );
		?>

		<input type="text" name="_ld_thrivecart_product_id" value="<?php echo esc_attr( $product_id ); ?>">

		<?php
	}

	/**
	 * Output courses selection field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_courses() {
		$courses      = $this->get_learndash_courses();
		$courses_meta = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_courses', true );
		?>

		<select name="_ld_thrivecart_courses[]" id="_ld_thrivecart_courses" multiple="multiple">
			<?php foreach ( $courses as $course_id => $course_title ) : ?>
				<?php $selected = in_array( $course_id, $courses_meta ) ? 'selected="selected"' : ''; ?>
			<option value="<?php echo esc_attr( $course_id ); ?>" <?php echo esc_attr( $selected ); ?>>
				<?php echo esc_attr( $course_title ); ?>
			</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Output groups selection field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_groups() {
		$groups      = $this->get_learndash_groups();
		$groups_meta = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_groups', true );
		?>

		<select name="_ld_thrivecart_groups[]" id="_ld_thrivecart_groups" multiple="multiple">
			<?php foreach ( $groups as $group_id => $group_title ) : ?>
				<?php $selected = in_array( $group_id, $groups_meta ) ? 'selected="selected"' : ''; ?>
			<option value="<?php echo esc_attr( $group_id ); ?>" <?php echo esc_attr( $selected ); ?>>
				<?php echo esc_attr( $group_title ); ?>
			</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Get learndash courses.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, string>
	 */
	public function get_learndash_courses() {
		$ld_courses = get_posts(
			[
				'post_type'      => 'sfwd-courses',
				'posts_per_page' => -1,
			]
		);

		$courses = [];
		foreach ( $ld_courses as $ld_course ) {
			$courses[ $ld_course->ID ] = $ld_course->post_title;
		}

		return $courses;
	}

	/**
	 * Get learndash groups.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, string>
	 */
	public function get_learndash_groups() {
		$ld_groups = get_posts(
			[
				'post_type'      => 'groups',
				'posts_per_page' => -1,
			]
		);

		$groups = [];
		foreach ( $ld_groups as $ld_group ) {
			$groups[ $ld_group->ID ] = $ld_group->post_title;
		}

		return $groups;
	}
}

new LearnDash_Thrivecart_Product();
