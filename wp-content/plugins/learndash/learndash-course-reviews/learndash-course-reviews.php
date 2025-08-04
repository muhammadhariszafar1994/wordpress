<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://www.learndash.com
 * @since 1.0.0
 * @package Course_Reviews
 *
 * Plugin Name:       LearnDash Course Reviews
 * Plugin URI:        TODO: Replace
 * Description:       Adds reviews to LearnDash Courses
 * Version: 1.0.2
 * Author:            LearnDash
 * Author URI:        https://www.learndash.com
 * Text Domain:       learndash-course-reviews
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'LEARNDASH_COURSE_REVIEWS_VERSION', '1.0.2' );
define( 'LEARNDASH_COURSE_REVIEWS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_COURSE_REVIEWS_URL', plugins_url( '/', __FILE__ ) );
define( 'LEARNDASH_COURSE_REVIEWS_FILE', __FILE__ );

if ( ! class_exists( 'LearnDash_Course_Reviews' ) ) {
	/**
	 * Main LearnDash_Course_Reviews class.
	 *
	 * @since 1.0.0
	 */
	final class LearnDash_Course_Reviews {
		/**
		 * Stores all our Admin Errors to fire at once.
		 *
		 * @var array<array{message: string, type: string}> $admin_notices
		 *
		 * @since 1.0.0
		 */
		private $admin_notices = array();

		/**
		 * RBM Field Helpers Object.
		 *
		 * @var RBM_FieldHelpers $field_helpers
		 *
		 * @since 1.0.0
		 */
		public $field_helpers;

		/**
		 * Get active instance.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return LearnDash_Course_Reviews Instance.
		 */
		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new static();
			}

			return $instance;
		}

		/**
		 * LearnDash_Course_Reviews constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->load_textdomain();

			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				$this->admin_notices[] = array(
					'message' => sprintf(
						// translators: First string is the plugin name, followed by the required WordPress version and then the anchor tag for a link to the Update screen.
						__( '%1$s requires v%2$s of %3$sWordPress%4$s or higher to be installed!', 'learndash-course-reviews' ),
						'<strong>LearnDash Course Reviews</strong>',
						'4.4',
						'<a href="' . admin_url( 'update-core.php' ) . '"><strong>',
						'</strong></a>'
					),
					'type'    => 'error',
				);

				if ( ! has_action( 'admin_notices', array( $this, 'admin_notices' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				}

				return;
			}

			if ( ! class_exists( 'Semper_Fi_Module' ) ) {
				$this->admin_notices[] = array(
					'message' => sprintf(
						// translators: First string is the current Plugin's name, followed by a link to the required plugin's website.
						__( '%1$s requires %2$s to be installed!', 'learndash-course-reviews' ),
						'<strong>LearnDash Course Reviews</strong>',
						'<a href="//www.learndash.com/" target="_blank"><strong>' . __( 'LearnDash', 'learndash-course-reviews' ) . '</strong></a>'
					),
					'type'    => 'error',
				);

				if ( ! has_action( 'admin_notices', array( $this, 'admin_notices' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				}

				return;
			}

			$this->require_necessities();

			// Register our CSS/JS for the whole plugin.
			add_action( 'init', array( $this, 'register_scripts' ) );
		}

		/**
		 * Internationalization.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function load_textdomain() {
			// Set filter for language directory.
			$lang_dir = trailingslashit( LEARNDASH_COURSE_REVIEWS_DIR ) . 'languages/';

			/**
			 * Filters the default languages directory.
			 *
			 * @since 1.0.0
			 *
			 * @param string $lang_dir Directory path.
			 *
			 * @return string Directory path.
			 */
			$lang_dir = apply_filters(
				'learndash_course_reviews_languages_directory',
				$lang_dir
			);

			/** This filter is documented in wp-includes/l10n.php */
			$locale = apply_filters(
				'plugin_locale', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Built-in WP Core Filter
				get_locale(),
				'learndash-course-reviews'
			);

			$mofile = sprintf(
				'%1$s-%2$s.mo',
				'learndash-course-reviews',
				$locale
			);

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = trailingslashit( WP_LANG_DIR ) . 'learndash-course-reviews/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/learndash-course-reviews/ folder.
				// This way translations can be overridden via the Theme/Child Theme.
				load_textdomain( 'learndash-course-reviews', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/learndash-course-reviews/languages/ folder.
				load_textdomain( 'learndash-course-reviews', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'learndash-course-reviews', false, $lang_dir );
			}
		}

		/**
		 * Include different aspects of the Plugin.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function require_necessities() {
			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/library/rbm-field-helpers/rbm-field-helpers.php';

			$this->field_helpers = new RBM_FieldHelpers(
				array(
					'ID'   => 'rbm_ld_reviews', // Your Theme/Plugin uses this to differentiate its instance of RBM FH from others when saving/grabbing data.
					'file' => LEARNDASH_COURSE_REVIEWS_FILE,
					'l10n' => array(
						'field_table'    => array(
							'delete_row'    => __( 'Delete Row', 'learndash-course-reviews' ),
							'delete_column' => __( 'Delete Column', 'learndash-course-reviews' ),
						),
						'field_select'   => array(
							'no_options'       => __( 'No select options.', 'learndash-course-reviews' ),
							'error_loading'    => __( 'The results could not be loaded', 'learndash-course-reviews' ),
							/* translators: %d is number of characters over input limit */
							'input_too_long'   => __( 'Please delete %d character(s)', 'learndash-course-reviews' ),
							/* translators: %d is number of characters under input limit */
							'input_too_short'  => __( 'Please enter %d or more characters', 'learndash-course-reviews' ),
							'loading_more'     => __( 'Loading more results...', 'learndash-course-reviews' ),
							/* translators: %d is maximum number items selectable */
							'maximum_selected' => __( 'You can only select %d item(s)', 'learndash-course-reviews' ),
							'no_results'       => __( 'No results found', 'learndash-course-reviews' ),
							'searching'        => __( 'Searching...', 'learndash-course-reviews' ),
						),
						'field_repeater' => array(
							'collapsable_title' => __( 'New Row', 'learndash-course-reviews' ),
							'confirm_delete'    => __( 'Are you sure you want to delete this element?', 'learndash-course-reviews' ),
							'delete_item'       => __( 'Delete', 'learndash-course-reviews' ),
							'add_item'          => __( 'Add', 'learndash-course-reviews' ),
						),
						'field_media'    => array(
							'button_text'        => __( 'Upload / Choose Media', 'learndash-course-reviews' ),
							'button_remove_text' => __( 'Remove Media', 'learndash-course-reviews' ),
							'window_title'       => __( 'Choose Media', 'learndash-course-reviews' ),
						),
						'field_checkbox' => array(
							'no_options_text' => __( 'No options available.', 'learndash-course-reviews' ),
						),
					),
				)
			);

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/learndash-course-reviews-field-helper-functions.php';

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/class-learndash-course-reviews-walker.php';

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/class-learndash-course-reviews-loader.php';

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/class-learndash-course-reviews-rest.php';

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/admin/class-learndash-course-reviews-comment-edit.php';

			require_once LEARNDASH_COURSE_REVIEWS_DIR . 'core/admin/class-learndash-course-reviews-course-edit.php';
		}

		/**
		 * Outputs Admin Notices.
		 * This is useful if you're too early in execution to use the add_settings_error() function as you can save them for later
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_notices() {
			foreach ( $this->admin_notices as $admin_notice ) :
				?>
				<div class="<?php echo esc_attr( $admin_notice['type'] ); ?> learndash-course-reviews-notice">
					<p>
						<?php echo wp_kses_post( $admin_notice['message'] ); ?>
					</p>
				</div>
				<?php
			endforeach;

			$this->admin_notices = array();
		}

		/**
		 * Register our CSS/JS to use later.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function register_scripts() {
			wp_register_style(
				'learndash-course-reviews',
				LEARNDASH_COURSE_REVIEWS_URL . 'dist/styles.css',
				array(),
				defined( 'LEARNDASH_SCRIPT_DEBUG' ) && LEARNDASH_SCRIPT_DEBUG ? strval( time() ) : LEARNDASH_COURSE_REVIEWS_VERSION
			);

			wp_register_script(
				'learndash-course-reviews',
				LEARNDASH_COURSE_REVIEWS_URL . 'dist/scripts.js',
				array( 'jquery' ),
				defined( 'LEARNDASH_SCRIPT_DEBUG' ) && LEARNDASH_SCRIPT_DEBUG ? strval( time() ) : LEARNDASH_COURSE_REVIEWS_VERSION,
				true
			);

			wp_localize_script(
				'learndash-course-reviews',
				'learndashCourseReviews',
				array(
					'restURL' => esc_url_raw( rest_url() ) . 'learndashCourseReviews/v1/',
				)
			);
		}
	}
} // End Class Exists Check

add_action( 'plugins_loaded', 'learndash_course_reviews_load' );

/**
 * The main function responsible for returning the one true LearnDash_Course_Reviews instance to functions everywhere.
 *
 * @since 1.0.0
 *
 * @return void
 */
function learndash_course_reviews_load(): void {
	require_once trailingslashit( __DIR__ ) . 'core/learndash-course-reviews-functions.php';
	LEARNDASHCOURSEREVIEWS();
}
