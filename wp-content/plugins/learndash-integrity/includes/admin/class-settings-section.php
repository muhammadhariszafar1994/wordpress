<?php

namespace LearnDash\Integrity\Admin;

use LearnDash_Settings_Section;
use LearnDash\Integrity\Prevent_Concurrent_Login;

/**
 * LearnDash Settings Section for Integrity.
 *
 * @package    LearnDash
 * @subpackage Settings
 */

/**
 * Class to create the settings section.
 */
class Settings_Section extends LearnDash_Settings_Section {

	/**
	 * Protected constructor for class
	 */
	protected function __construct() {
		$this->settings_page_id = 'learndash_lms_settings_integrity';

		// This is the 'option_name' key used in the wp_options table.
		$this->setting_option_key = 'learndash_settings_ld_integrity';

		// This is the HTML form field prefix used.
		$this->setting_field_prefix = 'learndash_settings_ld_integrity';

		// Used within the Settings API to uniquely identify this section.
		$this->settings_section_key = 'settings_integrity';

		// Section label/header.
		$this->settings_section_label = esc_html__( 'Integrity Settings', 'learndash-integrity' );

		parent::__construct();
	}

	/**
	 * Initialize the metabox settings values.
	 */
	public function load_settings_values() {
		parent::load_settings_values();

		if ( false === $this->setting_option_values ) {
			$sfwd_cpt_options = get_option( 'sfwd_cpt_options' );

			if ( ( isset( $sfwd_cpt_options['modules']['sfwd-courses_options'] ) ) && ( ! empty( $sfwd_cpt_options['modules']['sfwd-courses_options'] ) ) ) {
				foreach ( $sfwd_cpt_options['modules']['sfwd-courses_options'] as $key => $val ) {
					$key = str_replace( 'sfwd-courses_', '', $key );

					$this->setting_option_values[ $key ] = $val;
				}
			}
		}

		if ( ! isset( $this->setting_option_values['recaptcha'] ) ) {
			$this->setting_option_values['recaptcha'] = '';
		}

		if ( ! isset( $this->setting_option_values['v3'] ) ) {
			$this->setting_option_values['v3'] = '';
		}

		if ( ! isset( $this->setting_option_values['v2'] ) ) {
			$this->setting_option_values['v2'] = '';
		}

		if ( ! isset( $this->setting_option_values['prevent_concurrent_login'] ) ) {
			$this->setting_option_values['prevent_concurrent_login'] = '';
		}

		if ( ! isset( $this->setting_option_values['prevent_concurrent_login_exclude_roles'] ) ) {
			$this->setting_option_values['prevent_concurrent_login_exclude_roles'] = '';
		}
	}

	/**
	 * Initialize the metabox settings fields.
	 */
	public function load_settings_fields() {

		$prevent_concurrent_login_exclude_roles = array();
		$prevent_concurrent_login_exclude_roles = wp_roles()->get_names();

		$this->setting_option_fields = array(
			'prevent_hotlinking'       => array(
				'name'      => 'prevent_hotlinking',
				'type'      => 'checkbox-switch',
				'label'     => esc_html__( 'Prevent Hotlinking', 'learndash-integrity' ),
				'help_text' => esc_html__( 'Protect videos and images hosted on this site from hotlinking.',
				                           'learndash-integrity' ),
				'value'     => $this->setting_option_values['prevent_hotlinking'] ?? '',
				'options'   => array(
					'yes' => '',
					''    => ''
				),
			),
			'prevent_concurrent_login' => array(
				'name'      => 'prevent_concurrent_login',
				'type'      => 'checkbox-switch',
				'label'     => esc_html__( 'Prevent Concurrent Login', 'learndash-integrity' ),
				'help_text' => esc_html__( 'Enable concurrent logins per user.', 'learndash-integrity' ),
				'value'     => $this->setting_option_values['prevent_concurrent_login'] ?? '',
				'options'   => array(
					'yes' => '',
					''    => ''
				),
				'child_section_state' => ( 'yes' === $this->setting_option_values['prevent_concurrent_login'] ) ? 'open' : 'closed',
			),
			'prevent_concurrent_login_exclude_roles'        => array(
				'name'      => 'prevent_concurrent_login_exclude_roles',
				'type'      => 'multiselect',
				'label'     => esc_html__( 'Exclude User Role(s)', 'learndash-integrity' ),
				'help_text' => esc_html__( 'Choose one or more user roles to exclude from the prevent concurrent login feature.', 'learndash-integrity' ),
				'value'     => $this->setting_option_values['prevent_concurrent_login_exclude_roles'],
				'options'   => $prevent_concurrent_login_exclude_roles,
				'parent_setting'    => 'prevent_concurrent_login',
			),
			'prevent_content_copy'     => array(
				'name'      => 'prevent_content_copy',
				'type'      => 'checkbox-switch',
				'label'     => esc_html__( 'Prevent Content Copy', 'learndash-integrity' ),
				'help_text' => esc_html__( 'Prevent content on this site from being copied.',
				                           'learndash-integrity' ),
				'value'     => $this->setting_option_values['prevent_content_copy'] ?? '',
				'options'   => array(
					'yes' => '',
					''    => ''
				),
			),
			'recaptcha'                => array(
				'name'                => 'recaptcha',
				'type'                => 'checkbox-switch',
				'label'               => esc_html__( 'Enable reCaptcha', 'learndash-integrity' ),
				'help_text'           => sprintf(
					__(
						'reCaptcha safeguards your website from fraud and abuse while providing a smooth user experience. Enable only one version of reCaptcha at a time. <a target="_blank" href="%s">Set it up at here.</a>',
						'learndash-integrity'
					),
					esc_attr( esc_url( 'https://www.google.com/recaptcha/admin/create' ) )
				),
				'value'               => $this->setting_option_values['recaptcha'] ?? '',
				'options'             => array(
					'yes' => '',
					''    => ''
				),
				'child_section_state' => ( 'yes' === $this->setting_option_values['recaptcha'] ) ? 'open' : 'closed',
			),
			'v3'                       => array(
				'name'                => 'v3',
				'type'                => 'checkbox-switch',
				'label'               => esc_html__( 'reCaptcha v3 (Invisible)', 'learndash-integrity' ),
				'value'               => $this->setting_option_values['v3'] ?? '',
				'options'             => array(
					'yes' => '',
					''    => ''
				),
				'parent_setting'      => 'recaptcha',
				'child_section_state' => ( 'yes' === $this->setting_option_values['v3'] ) ? 'open' : 'closed',
			),
			'site_key'                 => array(
				'name'           => 'site_key',
				'label'          => __( 'Site Key', 'learndash-integrity' ),
				'type'           => 'text',
				'value'          => $this->setting_option_values['site_key'] ?? '',
				'parent_setting' => 'v3',
			),
			'secret_key'               => array(
				'name'           => 'secret_key',
				'label'          => __( 'Secret Key', 'learndash-integrity' ),
				'type'           => 'text',
				'value'          => $this->setting_option_values['secret_key'] ?? '',
				'parent_setting' => 'v3',
			),
			'score_threshold'          => array(
				'name'           => 'score_threshold',
				'type'           => 'select',
				'label'          => __( 'Score', 'learndash-integrity' ),
				'help_text'      => __( 'Minimum score user has to get to be able to bypass reCAPTCHA verification. reCAPTCHA v3 returns a score (1.0 is very likely a good interaction, 0.0 is very likely a bot).',
				                        'learndash-integrity' ),
				'value'          => $this->setting_option_values['score_threshold'] ?? '',
				'default'        => '0.5',
				'options'        => array(
					'0'   => '0',
					'0.2' => '0.2',
					'0.5' => '0.5',
					'0.8' => '0.8',
					'1'   => '1'
				),
				'parent_setting' => 'v3',
			),
			'v2'                       => array(
				'name'                => 'v2',
				'type'                => 'checkbox-switch',
				'label'               => esc_html__( 'reCaptcha v2 (checkbox)', 'learndash-integrity' ),
				'value'               => $this->setting_option_values['v2'] ?? '',
				'options'             => array(
					'yes' => '',
					''    => ''
				),
				'parent_setting'      => 'recaptcha',
				'child_section_state' => ( 'yes' === $this->setting_option_values['v2'] ) ? 'open' : 'closed',
			),
			'site_key_v2'              => array(
				'name'           => 'site_key_v2',
				'label'          => __( 'Site Key', 'learndash-integrity' ),
				'type'           => 'text',
				'value'          => $this->setting_option_values['site_key_v2'] ?? '',
				'parent_setting' => 'v2',
			),
			'secret_key_v2'            => array(
				'name'           => 'secret_key_v2',
				'label'          => __( 'Secret Key', 'learndash-integrity' ),
				'type'           => 'text',
				'value'          => $this->setting_option_values['secret_key_v2'] ?? '',
				'parent_setting' => 'v2',
			),
			'location'                 => array(
				'name'           => 'location',
				'label'          => __( 'Location', 'learndash-integrity' ),
				'help_text'      => __( 'Choose the forms for which reCaptcha will be displayed.',
				                        'learndash-integrity' ),
				'value'          => $this->setting_option_values['location'] ?? '',
				'type'           => 'checkbox',
				'options'        => array(
					'login'       => __( 'Login', 'learndash-integrity' ),
					'register'    => __( 'Register', 'learndash-integrity' )
				),
				'parent_setting' => 'recaptcha',
			),
		);

		$this->setting_option_fields = apply_filters( 'learndash_settings_fields',
		                                              $this->setting_option_fields,
		                                              $this->settings_section_key );

		parent::load_settings_fields();
	}
}

add_action( 'learndash_settings_sections_init', function () {
	Settings_Section::add_section_instance();
} );
