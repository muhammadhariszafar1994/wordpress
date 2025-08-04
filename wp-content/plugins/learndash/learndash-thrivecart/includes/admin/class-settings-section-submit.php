<?php
/**
 * Setting section submit class file.
 *
 * @package LearnDash\Thrivecart
 */

if (
	class_exists( 'LearnDash_Settings_Section' )
	&& ! class_exists( 'LearnDash_Thrivecart_Section_Submit' )
) {
	/**
	 * Setting section for submit section class.
	 *
	 * @since 1.0
	 */
	class LearnDash_Thrivecart_Section_Submit extends LearnDash_Settings_Section {
		public function __construct() {
			$this->settings_page_id = 'ld-thrivecart-settings';

			// This is the 'option_name' key used in the wp_options table
			$this->setting_option_key = 'submitdiv';

			// Section label/header
			$this->settings_section_label = esc_html__( 'Save Options', 'learndash-thrivecart' );

			$this->metabox_context  = 'side';
			$this->metabox_priority = 'high';

			parent::__construct();

			// We override the parent value set for $this->metabox_key because we want the div ID to match the details WordPress
			// value so it will be hidden.
			$this->metabox_key = 'submitdiv';
		}

		public function show_meta_box(): void {

			?>
			<div id="submitpost" class="submitbox">

				<div id="major-publishing-actions">
					<div id="publishing-action">
						<span class="spinner"></span>
						<?php submit_button( esc_attr( esc_html__( 'Save', 'learndash-thrivecart' ) ), 'primary', 'submit', false ); ?>
					</div>

					<div class="clear"></div>

				</div><!-- #major-publishing-actions -->

			</div><!-- #submitpost -->
			<?php
		}

		// This is a required function.
		public function load_settings_fields(): void {

		}
	}
}
add_action(
	'learndash_settings_sections_init',
	function() {
		LearnDash_Thrivecart_Section_Submit::add_section_instance();
	}
);
