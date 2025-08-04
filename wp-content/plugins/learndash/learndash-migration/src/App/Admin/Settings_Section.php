<?php
/**
 * Class file for settings section.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Migration
 */

namespace LearnDash\Migration\Admin;

use LDLMS_Post_Types;
use LearnDash\Core\Modules\AJAX\Search_Posts;
use LearnDash\Migration\AJAX\Migrate;
use LearnDash\Core\App;
use LearnDash\Migration\Integrations;
use LearnDash_Settings_Section;
use WP_Screen;

/**
 * Settings_Section class for Migration addon.
 *
 * @since 1.0.0
 */
class Settings_Section extends LearnDash_Settings_Section {
	/**
	 * Setting page URL query key.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $settings_sub_page_query_key = 'section-advanced';

	/**
	 * Protected constructor for class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings_page_id = 'learndash_lms_advanced';

		// This is the 'option_name' key used in the wp_options table.
		$this->setting_option_key = 'learndash_settings_migration';

		// This is the HTML form field prefix used.
		$this->setting_field_prefix = 'learndash_settings_migration';

		// Used within the Settings API to uniquely identify this section.
		$this->settings_section_key = 'settings_migration';

		// Section label/header.

		$this->settings_section_label     = esc_html__( 'Migration', 'learndash-migration' );
		$this->settings_section_sub_label = esc_html__( 'Migration', 'learndash-migration' );

		$this->settings_section_description = sprintf(
			// Translators: courses label.
			esc_html__( 'Migrate %s data from other LMS platforms to LearnDash.', 'learndash-migration' ),
			esc_html(
				learndash_get_custom_label_lower( 'courses' )
			)
		);

		parent::__construct();
	}

	/**
	 * Initialize the metabox settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_settings_fields(): void {
		/**
		 * Integrations object.
		 *
		 * @var Integrations $integrations_object Integrations object.
		 */
		$integrations_object = App::get( Integrations::class );
		$integrations        = $integrations_object->get_all();

		$lms_options = [
			'' => __( 'Available Integrations', 'learndash-migration' ),
		];

		foreach ( $integrations as $key => $integration ) {
			$lms_options[ $key ] = $integration->label;
		}

		$this->setting_option_fields = [
			'source'  => [
				'name'      => 'source',
				'type'      => 'select',
				'label'     => esc_html__( 'LMS', 'learndash-migration' ),
				'help_text' => esc_html__( 'Source LMS the data will be migrated from.', 'learndash-migration' ),
				'options'   => $lms_options,
				'value'     => '',
			],
			'course'  => [
				'name'      => 'course',
				'type'      => 'select',
				'label'     => esc_html(
					learndash_get_custom_label( 'course' )
				),
				'help_text' => sprintf(
					// Translators: course label.
					esc_html__( 'Select the %s you want to migrate.', 'learndash-migration' ),
					esc_html(
						learndash_get_custom_label_lower( 'course' )
					)
				),
				'options'   => [],
				'attrs'     => [
					'data-ld-select2' => '0',
				],
				'value'     => '',
			],
			'migrate' => [
				'name'             => 'migrate',
				'type'             => 'html',
				'label'            => '',
				'value'            => '',
				'display_callback' => [ $this, 'output_button' ],
			],
		];

		/** This filter is documented in LearnDash core includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
		$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

		parent::load_settings_fields();
	}

	/**
	 * Callback method to output button.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_button(): void {
		?>
		<button class="button button-primary" id="migrate" type="submit" name="migrate">
			<?php
			printf(
				// Translators: course label.
				esc_html__( 'Migrate %s', 'learndash-migration' ),
				esc_html(
					learndash_get_custom_label( 'course' )
				)
			);
			?>
		</button>
		<?php
	}

	/**
	 * Filter whether to show submit section on settings page or not.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $show              Original show value.
	 * @param string $settings_page_id  Settings page ID.
	 *
	 * @return bool  Modified show value.
	 */
	public function show_section_submit( $show, $settings_page_id ): bool {
		if (
			$settings_page_id === $this->settings_page_id
			&& isset( $_GET[ $this->settings_sub_page_query_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& $_GET[ $this->settings_sub_page_query_key ] === $this->settings_section_key // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return false;
		}

		return $show;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts(): void {
		$screen = get_current_screen();

		if (
			! ( $screen instanceof WP_Screen )
			|| $screen->id !== 'admin_page_' . $this->settings_page_id
			|| ! isset( $_GET[ $this->settings_sub_page_query_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| $_GET[ $this->settings_sub_page_query_key ] !== $this->settings_section_key // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return;
		}

		wp_enqueue_script(
			'learndash-migration-admin',
			LEARNDASH_MIGRATION_PLUGIN_URL . 'dist/js/admin/scripts' . learndash_min_asset() . '.js',
			[ 'jquery' ],
			LEARNDASH_MIGRATION_VERSION,
			true
		);

		wp_enqueue_style(
			'learndash-migration-admin',
			LEARNDASH_MIGRATION_PLUGIN_URL . 'dist/css/admin/styles' . learndash_min_asset() . '.css',
			[],
			LEARNDASH_MIGRATION_VERSION
		);

		$course_post_types = [];
		/**
		 * Integrations object.
		 *
		 * @var Integrations $integrations_object Integrations object.
		 */
		$integrations_object = App::get( Integrations::class );
		$integrations        = $integrations_object->get_all();

		foreach ( $integrations as $key => $integration ) {
			$course_post_types[ $key ] = $integration->mapped_post_types[ LDLMS_Post_Types::COURSE ];
		}

		wp_localize_script(
			'learndash-migration-admin',
			'LearnDashMigrationAdmin',
			[
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'text'              => [
					'migrate_course'       => sprintf(
						// Translators: course label.
						__( 'Migrate %s', 'learndash-migration' ),
						learndash_get_custom_label( 'course' )
					),
					'select_a_course'      => sprintf(
						// Translators: course label.
						__( 'Select a %s', 'learndash-migration' ),
						learndash_get_custom_label( 'course' )
					),
					'progress_status_init' => sprintf(
						// Translators: course label.
						__( 'Your %s is being migrated. Please wait a moment.', 'learndash-migration' ),
						learndash_get_custom_label_lower( 'course' )
					),
					'progress_status_end'  => sprintf(
						// Translators: 1$: course label, 2$: opening html tag, 3$: closing HTML tag, 4$: course label.
						__( 'Your %1$s has been successfully migrated. %2$sClick here%3$s to edit the %4$s.', 'learndash-migration' ),
						learndash_get_custom_label_lower( 'course' ),
						'<a class="new-course-url" href="#">',
						'</a>',
						learndash_get_custom_label_lower( 'course' )
					),
					'error_empty_source'   => __( 'Please select an integration first.', 'learndash-migration' ),
					'error_empty_course'   => sprintf(
						// Translators: course label.
						__( 'Please select a %s first.', 'learndash-migration' ),
						learndash_get_custom_label_lower( 'course' )
					),
				],
				'action'            => [
					'get_courses' => Search_Posts::$action,
					'migrate'     => Migrate::$action,
				],
				'nonce'             => [
					'get_courses' => wp_create_nonce( Search_Posts::$action ),
					'migrate'     => wp_create_nonce( Migrate::$action ),
				],
				'course_post_types' => $course_post_types,
			]
		);
	}
}
