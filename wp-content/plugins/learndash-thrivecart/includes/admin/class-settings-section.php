<?php
/**
 * Settings section class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Thrivecart
 */

if (
	class_exists( 'LearnDash_Settings_Section' )
	&& ! class_exists( 'LearnDash_Thrivecart_Settings_Section' )
) {
	/**
	 * Settings section class.
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Thrivecart_Settings_Section extends LearnDash_Settings_Section {

		function __construct() {
			$this->settings_page_id = 'ld-thrivecart-settings';

			// This is the 'option_name' key used in the wp_options table
			$this->setting_option_key = 'learndash_thrivecart_settings';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_thrivecart_settings';

			// Used within the Settings API to uniquely identify this section
			$this->settings_section_key = 'thrivecart_settings';

			// Section label/header
			$this->settings_section_label = esc_html__( 'Settings', 'learndash-thrivecart' );

			parent::__construct();
		}

		/**
		 * Load settings values.
		 *
		 * @since 1.0   Initial version.
		 * @since 1.0.2 Add `partial_refund_behavior` setting default value.
		 *
		 * @return void
		 */
		public function load_settings_values(): void {
			parent::load_settings_values();

			if ( $this->setting_option_values === false ) {
				$this->setting_option_values = array();
			}

			$this->setting_option_values = wp_parse_args(
				$this->setting_option_values,
				array(
					'secret_word'             => '',
					'partial_refund_behavior' => 'remove',
				)
			);
		}

		/**
		 * Load settings fields.
		 *
		 * @since 1.0 Initial version.
		 * @since 1.0.2 Add `partial_refund_behavior` setting.
		 *
		 * @return void
		 */
		public function load_settings_fields(): void {
			$this->setting_option_fields = array(
				'secret_word'             => array(
					'name'      => 'secret_word',
					'type'      => 'text',
					'label'     => esc_html__( 'Secret Word', 'learndash-thrivecart' ),
					'help_text' => esc_html__( 'Secret word from your ThriveCart account. You can get it from your account under Settings > API & Webhooks > ThriveCart API.', 'learndash-thrivecart' ),
					'value'     => isset( $this->setting_option_values['secret_word'] ) ? $this->setting_option_values['secret_word'] : '',
				),
				'webhook_url'             => array(
					'name'      => 'webhook_url',
					'type'      => 'text',
					'label'     => __( 'Webhook URL', 'learndash-thrivecart' ),
					'help_text' => __( 'URL that you need to paste into your Thrivecart account webhook URL setting.', 'learndash-thrivecart' ),
					'value'     => add_query_arg(
						array(
							'learndash-integration' => 'thrivecart',
						),
						home_url( '/' )
					),
					'attrs'     => array( 'readonly' => 'readonly' ),
				),
				'partial_refund_behavior' => [
					'name'      => 'partial_refund_behavior',
					'type'      => 'select',
					'label'     => esc_html__( 'Partial Refund Behavior', 'learndash-thrivecart' ),
					'help_text' => esc_html__( 'Behavior you want to happen when a user makes a partial refund. The default is to remove the user access from associated courses and groups.', 'learndash-thrivecart' ),
					'options'   => [
						'remove' => __( 'Remove user access from associated courses and groups', 'learndash-thrivecart' ),
						'keep'   => __( 'Keep user access to associated courses and groups', 'learndash-thrivecart' ),
					],
					'value'     => isset( $this->setting_option_values['partial_refund_behavior'] ) ? $this->setting_option_values['partial_refund_behavior'] : 'remove',
					'attrs'     => [
						'data-ld-select2' => 0,
					],
				],
			);

			$this->setting_option_fields = apply_filters( 'learndash_thrivecart_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}
	}
}
add_action(
	'learndash_settings_sections_init',
	function() {
		LearnDash_Thrivecart_Settings_Section::add_section_instance();
	}
);
