<?php
namespace LearnDash\_2Checkout;

/**
 * LearnDash Settings Section for PayPal Metabox.
 *
 * @since 1.2.0
 * @package LearnDash\Settings\Sections
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LearnDash_Settings_Section;

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) ) {

	/**
	 * Class LearnDash Settings Section for PayPal Metabox.
	 *
	 * @since 1.2.0
	 */
	class Settings_Section extends LearnDash_Settings_Section {

		/**
		 * Protected constructor for class
		 *
		 * @since 1.2.0
		 */
		protected function __construct() {
			$this->settings_page_id = 'learndash_lms_payments';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_2checkout_settings';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_2checkout_settings';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'settings_2checkout';

			// Section label/header.
			$this->settings_section_label = esc_html__( '2Checkout Settings', 'learndash-2checkout' );

			$this->reset_confirm_message = esc_html__( 'Are you sure want to reset the 2Checkout values?', 'learndash-2checkout' );

			// Used to associate this section with the parent section.
			$this->settings_parent_section_key = 'settings_payments_list';

			$this->settings_section_listing_label = esc_html__( '2Checkout', 'learndash-2checkout' );

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 1.2.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			$new_settings = false;

			if ( false === $this->setting_option_values ) {
                $options = get_option( 'learndash_2checkout_settings', [] );
				if ( ( isset( $options ) ) && ( ! empty( $options ) ) ) {
					foreach ( $options as $key => $value ) {
						if ( 'demo' === $key ) {
							if ( 1 == $value ) {
								$value = 'yes';
							} else {
								$value = 'no';
							}
						}

						$this->setting_option_values[ $key ] = $value;
					}
				}
			}

			if ( ( isset( $_GET['action'] ) ) && ( 'ld_reset_settings' === $_GET['action'] ) && ( isset( $_GET['page'] ) ) && ( $_GET['page'] == $this->settings_page_id ) ) {
				if ( ( isset( $_GET['ld_wpnonce'] ) ) && ( ! empty( $_GET['ld_wpnonce'] ) ) ) {
					if ( wp_verify_nonce( $_GET['ld_wpnonce'], get_current_user_id() . '-' . $this->setting_option_key ) ) {
						if ( ! empty( $this->setting_option_values ) ) {
							foreach ( $this->setting_option_values as $key => $val ) {
								$this->setting_option_values[ $key ] = '';
							}
							$this->save_settings_values();
						}

						$reload_url = remove_query_arg( array( 'action', 'ld_wpnonce' ) );
						learndash_safe_redirect( $reload_url );
					}
				}
			}

			if ( ! isset( $this->setting_option_values['enabled'] ) ) {
				if ( ( ! empty( $this->setting_option_values['sid'] ) ) && ( ! empty( $this->setting_option_values['secret_word'] ) ) ) {
					$this->setting_option_values['enabled'] = 'yes';
				} else {
					$this->setting_option_values['enabled'] = '';
				}
			}
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 1.2.0
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'enabled' => array(
					'name'    => 'enabled',
					'type'    => 'checkbox-switch',
					'label'   => esc_html__( 'Active', 'learndash-2checkout' ),
					'value'   => $this->setting_option_values['enabled'],
					'default' => '',
					'options' => array(
						'on' => '',
						''   => '',
					),
				),
                'demo'   => array(
					'name'              => 'demo',
					'type'              => 'checkbox-switch',
					'label'             => esc_html__( 'Test Mode', 'learndash-2checkout' ),
					'help_text'         => __( 'Enable to activate test mode.', 'learndash-2checkout' ),
					'value'             => ( ( isset( $this->setting_option_values['demo'] ) ) && ( ! empty( $this->setting_option_values['demo'] ) ) ) ? $this->setting_option_values['demo'] : '',
                    'default' => '',
					'options' => array(
						'yes' => '',
                        ''    => ''
					),
				),
				'sid' => array(
					'name'              => 'sid',
					'type'              => 'text',
					'label'             => esc_html__( 'Merchant Code', 'learndash-2checkout' ),
					'help_text'         => esc_html__( 'Enter your 2Checkout merchant code here.', 'learndash-2checkout' ),
					'value'             => ( ( isset( $this->setting_option_values['sid'] ) ) && ( ! empty( $this->setting_option_values['sid'] ) ) ) ? $this->setting_option_values['sid'] : '',
					'class'             => 'regular-text',
				),
				'secret_word'  => array(
					'name'              => 'secret_word',
					'type'              => 'text',
					'label'             => esc_html__( 'Buy Link Secret Word', 'learndash-2checkout' ),
					'help_text'         => __( 'This 
                    must matche with the Buy Link secret word in your 2Checkout account.', 'learndash-2checkout' ),
					'value'             => ( ( isset( $this->setting_option_values['secret_word'] ) ) && ( ! empty( $this->setting_option_values['secret_word'] ) ) ) ? $this->setting_option_values['secret_word'] : '',
					'class'             => 'regular-text',
				),
			);

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}


		/**
		 * Filter the section saved values.
		 * 
		 * @since 3.6.0
		 * 
		 * @param array  $value                An array of setting fields values.
		 * @param array  $old_value            An array of setting fields old values.
		 * @param string $settings_section_key Settings section key.
		 * @param string $settings_screen_id   Settings screen ID.
		 */
		public function filter_section_save_fields( $value, $old_value, $settings_section_key, $settings_screen_id ) {
			if ( $settings_section_key === $this->settings_section_key ) {
				if ( ! isset( $value['enabled'] ) ) {
					$value['enabled'] = '';
				}

				if ( isset( $_POST['learndash_settings_payments_list_nonce'] ) ) {
					if ( ! is_array( $old_value ) ) {
						$old_value = array();
					}

					foreach( $value as $value_idx => $value_val ) {
						$old_value[ $value_idx ] = $value_val;
					}

					$value = $old_value;
				}
			}

			return $value;
		}
	}
}

add_action(
	'learndash_settings_sections_init',
	function() {
		Settings_Section::add_section_instance();
	}
);
