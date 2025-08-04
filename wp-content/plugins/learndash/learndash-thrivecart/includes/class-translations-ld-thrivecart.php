<?php
/**
 * Translation class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Thrivecart
 */

if (
	class_exists( 'LearnDash_Settings_Section' )
	&& ! class_exists( 'LearnDash_Settings_Section_Translations_Learndash_Thrivecart' )
) {
	/**
	 * Translations class.
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Settings_Section_Translations_Learndash_Thrivecart extends LearnDash_Settings_Section {
		/**
		 * Plugin text domain.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		private $project_slug = 'learndash-thrivecart';

		/**
		 * Flag to mark if translation has been registered.
		 *
		 * @since 1.0.0
		 *
		 * @var boolean
		 */
		private $registered   = false;

		function __construct() {
			$this->settings_page_id = 'learndash_lms_translations';

			// Used within the Settings API to uniquely identify this section
			$this->settings_section_key = 'settings_translations_' . $this->project_slug;

			// Section label/header
			$this->settings_section_label = __( 'LearnDash Thrivecart', 'learndash-thrivecart' );

			// Class LearnDash_Translations add LD v2.5.0
			if ( class_exists( 'LearnDash_Translations' ) ) {
				// Method register_translation_slug add LD v2.5.5
				if ( method_exists( 'LearnDash_Translations', 'register_translation_slug' ) ) {
					$this->registered = true;
					LearnDash_Translations::register_translation_slug( $this->project_slug, LEARNDASH_THRIVECART_PLUGIN_PATH . 'languages' );
				}
			}

			parent::__construct();
		}

		public function add_meta_boxes( $settings_screen_id = '' ): void {
			if ( ( $settings_screen_id == $this->settings_screen_id ) && ( $this->registered === true ) ) {
				parent::add_meta_boxes( $settings_screen_id );
			}
		}

		public function show_meta_box(): void {
			$ld_translations = new LearnDash_Translations( $this->project_slug );
			$ld_translations->show_meta_box();
		}
	}
	add_action(
		'init',
		function() {
			LearnDash_Settings_Section_Translations_Learndash_Thrivecart::add_section_instance();
		}
	);
}

