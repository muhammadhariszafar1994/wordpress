<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'Settings_Section_Stripe' ) ) ) {
	class Settings_Section_Stripe extends LearnDash_Settings_Section {
		/**
		 * Protected constructor for class
		 *
		 * @since 2.4.0
		 */
		protected function __construct() {
			$this->settings_page_id = 'learndash_lms_payments';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_stripe_settings';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_settings_stripe';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'settings_stripe';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Stripe Settings', 'learndash-stripe' );

			$this->reset_confirm_message = esc_html__( 'Are you sure want to reset the Stripe values?', 'learndash' );

			// Used to associate this section with the parent section.
			$this->settings_parent_section_key = 'settings_payments_list';

			$this->settings_section_listing_label = esc_html__( 'Stripe', 'learndash-stripe' );

			parent::__construct();
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 2.4.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			if ( ! isset( $this->setting_option_values['test_mode'] ) ) {
				$this->setting_option_values['test_mode'] = '';
			}

			if ( isset( $this->setting_option_values['payment_methods'] ) &&
			     is_array( $this->setting_option_values['payment_methods'] )
			     && count( $this->setting_option_values['payment_methods'] ) ) {
				// backward compatibility as the older has different format
				foreach ( $this->setting_option_values['payment_methods'] as $key => $value ) {
					if ( in_array( $key, array( 'card', 'ideal' ), true ) ) {
						// this is old data
						$this->setting_option_values['payment_methods'] = array_keys( $this->setting_option_values['payment_methods'] );
						break;
					}
				}
			}

			if ( ( isset( $_GET['action'] ) ) && ( 'ld_reset_settings' === $_GET['action'] ) && ( isset( $_GET['page'] ) ) && ( $_GET['page'] == $this->settings_page_id ) ) {
				if ( ( isset( $_GET['ld_wpnonce'] ) ) && ( ! empty( $_GET['ld_wpnonce'] ) ) ) {
					if ( wp_verify_nonce( $_GET['ld_wpnonce'],
					                      get_current_user_id() . '-' . $this->setting_option_key ) ) {
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
				$live_key        = $this->setting_option_values['publishable_key_live'] ?? '';
				$live_secret     = $this->setting_option_values['secret_key_live'] ?? '';
				$endpoint_secret = $this->setting_option_values['endpoint_secret'] ?? '';

				$test_key             = $this->setting_option_values['publishable_key_test'] ?? '';
				$test_secret          = $this->setting_option_values['secret_key_test'] ?? '';
				$test_endpoint_secret = $this->setting_option_values['endpoint_secret_test'] ?? '';


				if ( ( $live_key && $live_secret && $endpoint_secret ) || ( $test_key && $test_secret && $test_endpoint_secret ) ) {
					$this->setting_option_values['enabled'] = 'yes';
				} else {
					$this->setting_option_values['enabled'] = '';
				}
			}
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 2.4.0
		 */
		public function load_settings_fields() {
			$this->setting_option_fields = array(
				'enabled'   => array(
					'name'    => 'enabled',
					'type'    => 'checkbox-switch',
					'label'   => esc_html__( 'Active', 'learndash-stripe' ),
					'value'   => $this->setting_option_values['enabled'] ?? '',
					'default' => '',
					'options' => array(
						'yes' => '',
						''    => '',
					),
				),
				'test_mode' => array(
					'name'                => 'test_mode',
					'label'               => esc_html__( 'Test Mode', 'learndash-stripe' ),
					'help_text'           => esc_html__( 'Check this box to enable test mode.', 'learndash-stripe' ),
					'type'                => 'checkbox-switch',
					'options'             => array(
						'1' => '',
						'0' => '',
					),
					'default'             => '',
					'value'               => $this->setting_option_values['test_mode'] ?? 0,
					'child_section_state' => ( '1' === $this->setting_option_values['test_mode'] ) ? 'open' : 'closed',
				),

				'publishable_key_test' => array(
					'name'           => 'publishable_key_test',
					'label'          => __( 'Test Publishable Key', 'learndash-stripe' ),
					'help_text'      => __( 'Test publishable key used in test mode.', 'learndash-stripe' ),
					'type'           => 'text',
					'value'          => $this->setting_option_values['publishable_key_test'] ?? '',
					'parent_setting' => 'test_mode',
				),
				'secret_key_test'      => array(
					'name'           => 'secret_key_test',
					'label'          => __( 'Test Secret Key', 'learndash-stripe' ),
					'help_text'      => __( 'Test secret key used in test mode.', 'learndash-stripe' ),
					'type'           => 'text',
					'value'          => $this->setting_option_values['secret_key_test'] ?? '',
					'parent_setting' => 'test_mode',
				),
				'endpoint_secret_test' => array(
					'name'           => 'endpoint_secret_test',
					'label'          => __( 'Test Endpoint Secret', 'learndash-stripe' ),
					'help_text'      => __( 'Test secret strings from your Stripe\'s webhook settings.',
					                        'learndash-stripe' ),
					'type'           => 'text',
					'value'          => $this->setting_option_values['endpoint_secret_test'] ?? '',
					'parent_setting' => 'test_mode',
				),
				'integration_type'     => array(
					'name'      => 'integration_type',
					'label'     => esc_html__( 'Integration Type', 'learndash-stripe' ),
					'help_text' => esc_html__( 'Stripe integration used on this site.', 'learndash-stripe' ),
					'type'      => 'select',
					'options'   => array(
						'legacy_checkout' => esc_html__( 'Legacy Checkout', 'learndash-stripe' ),
						'checkout'        => esc_html__( 'Checkout (Support SCA)', 'learndash-stripe' )
					),
					'value'     => $this->setting_option_values['integration_type'] ?? 'legacy_checkout',
				),
				'publishable_key_live' => array(
					'name'      => 'publishable_key_live',
					'label'     => __( 'Live Publishable Key', 'learndash-stripe' ),
					'help_text' => __( 'Live publishable key used in real transaction.', 'learndash-stripe' ),
					'type'      => 'text',
					'value'     => $this->setting_option_values['publishable_key_live'] ?? '',
				),
				'secret_key_live'      => array(
					'name'      => 'secret_key_live',
					'label'     => __( 'Live Secret Key', 'learndash-stripe' ),
					'help_text' => __( 'Live secret key used in real transaction.', 'learndash-stripe' ),
					'type'      => 'text',
					'value'     => $this->setting_option_values['secret_key_live'] ?? '',
				),
				'endpoint_secret'      => array(
					'name'      => 'endpoint_secret',
					'label'     => __( 'Endpoint Secret', 'learndash-stripe' ),
					'help_text' => __( 'Secret strings from your Stripe\'s webhook settings.', 'learndash-stripe' ),
					'type'      => 'text',
					'value'     => $this->setting_option_values['endpoint_secret'] ?? '',
				),
				'webhook_url'          => array(
					'name'       => 'webhook_url',
					'label'      => __( 'Webhook URL', 'learndash-stripe' ),
					'help_text'  => __( 'This URL needs to pasted to your Stripe account\'s webhook settings.',
					                    'learndash-stripe' ),
					'type'       => 'text',
					'value'      => add_query_arg( array( 'learndash-integration' => 'stripe' ),
					                               trailingslashit( home_url() ) ),
					'attributes' => array(
						'readonly' => 'readonly',
						'disabled' => 'disabled',
					)
				),
				'currency'             => array(
					'name'      => 'currency',
					'label'     => __( 'Currency', 'learndash-stripe' ),
					'help_text' => sprintf( __( '3-letter ISO code for currency, <a href="%1$s" target="%2$s">click here</a> for more information.',
					                            'learndash-stripe' ),
					                        'https://support.stripe.com/questions/which-currencies-does-stripe-support',
					                        '_blank' ),
					'type'      => 'text',
					'value'     => $this->setting_option_values['currency'] ?? '',
				),
				'payment_methods'      => array(
					'name'      => 'payment_methods',
					'label'     => __( 'Payment Methods', 'learndash-stripe' ),
					'help_text' => __( 'Stripe payment methods to be enabled on the site.', 'learndash-stripe' ),
					'value'     => $this->setting_option_values['payment_methods'] ?? '',
					'type'      => 'checkbox',
					'options'   => array(
						'card'  => __( 'Credit Card', 'learndash-stripe' ),
						'ideal' => __( 'Ideal', 'learndash-stripe' ),
					),
				),
				'return_url'           => array(
					'name'      => 'return_url',
					'label'     => __( 'Return URL ', 'learndash-stripe' ),
					'help_text' => __( 'Redirect the user to a specifici URL after the purchase. Leave blank to let user remain on the Course page',
					                   'learndash-stripe' ),
					'type'      => 'text',
					'value'     => $this->setting_option_values['return_url'] ?? '',
				),
			);

			// remove currency if LearnDash have payments defaults configurations.
			if ( class_exists( 'LearnDash_Settings_Section_Payments_Defaults' ) ) {
				unset( $this->setting_option_fields['currency'] );
			}

			/** This filter is documented in includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields',
			                                              $this->setting_option_fields,
			                                              $this->settings_section_key );
			parent::load_settings_fields();
		}

		/**
		 * Filter the section saved values.
		 *
		 * @param array  $value                An array of setting fields values.
		 * @param array  $old_value            An array of setting fields old values.
		 * @param string $settings_section_key Settings section key.
		 * @param string $settings_screen_id   Settings screen ID.
		 *
		 * @since 3.6.0
		 *
		 */
		public function filter_section_save_fields( $value, $old_value, $settings_section_key, $settings_screen_id ) {
			if ( $settings_section_key === $this->settings_section_key ) {
				if ( ! isset( $value['enabled'] ) ) {
					$value['enabled'] = '';
				}
				if ( isset( $value['payment_methods'] ) && is_array( $value['payment_methods'] ) ) {
					$payment_methods = array_fill_keys( $value['payment_methods'], 1 );
					// convert it to the old format.
					$value['payment_methods'] = $payment_methods;
				}
				if ( isset( $_POST['learndash_settings_payments_list_nonce'] ) ) {
					if ( ! is_array( $old_value ) ) {
						$old_value = array();
					}

					foreach ( $value as $value_idx => $value_val ) {
						$old_value[ $value_idx ] = $value_val;
					}

					$value = $old_value;
				}
			}

			return $value;
		}
	}

	add_action(
		'learndash_settings_sections_init',
		function () {
			// only if LD 3.6
			if ( defined( 'LEARNDASH_VERSION' ) && version_compare( LEARNDASH_VERSION, '3.6', '>=' ) ) {
				Settings_Section_Stripe::add_section_instance();
			}
		}
	);
}
