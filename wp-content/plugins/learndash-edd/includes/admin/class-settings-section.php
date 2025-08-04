<?php
namespace LearnDash\Easy_Digital_Downloads\Admin;

/**
 * LearnDash Settings Section for REST API Metabox.
 *
 * @since 1.5.0
 * @package LearnDash\Settings\Sections
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LearnDash_Settings_Section;
use LearnDash_Custom_Label;

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) ) {
	/**
	 * Class LearnDash Settings Section for REST API Metabox.
	 *
	 * @since 1.5.0
	 */
	class Settings_Section extends LearnDash_Settings_Section {

		/**
		 * Setting Option Fields REST API V1
		 *
		 * @var array
		 */
		protected $setting_option_fields_v1 = array();

		/**
		 * Setting Option Fields REST API V2
		 *
		 * @var array
		 */
		protected $setting_option_fields_v2 = array();

		/**
		 * Protected constructor for class
		 *
		 * @since 1.5.0
		 */
		protected function __construct() {
			$this->settings_page_id = 'learndash_lms_advanced';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_edd_settings';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'learndash_edd_settings';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'settings_edd';

			// Section label/header.
			$this->settings_section_label     = esc_html__( 'Easy Digital Downloads Integration Settings', 'learndash-edd' );
			$this->settings_section_sub_label = esc_html__( 'Easy Digital Downloads', 'learndash-edd' );

			$this->settings_section_description = esc_html__( 'Configure Easy Digital Downloads integration.', 'learndash-edd' );

			parent::__construct();

            add_filter( 'learndash_settings_show_section_submit', [ $this, 'show_section_submit' ], 10, 2 );
		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 1.5.0
		 */
		public function load_settings_values() {
			parent::load_settings_values();

			$this->setting_option_values = apply_filters( 'learndash_rest_settings_values', $this->setting_option_values );
		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 1.5.0
		 */
		public function load_settings_fields() {

			$this->setting_option_fields = array(
				'retroactive_tool' => array(
					'name'      => 'retroactive_tool',
					'type'      => 'html',
					'label'     => esc_html__( 'Retroactive Tool', 'learndash-edd' ),
                    'value'     => '<button class="ld-edd-retroactive-tool button button-secondary">' . __( 'Run', 'learndash-edd' ) . '</button>',
					'help_text' => esc_html__( 'Run this tool to enroll users in and unenroll users from course(s) and group(s) according to their EDD purchases.', 'learndash-edd' ),
					'attrs'     => array(),
				),
			);
			
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();
		}

        /**
         * Filter whether to show submit section on settings page or not
         *
         * @param bool   $show
         * @param string $settings_page_id
         * @return bool  Modified $show value
         */
        public function show_section_submit( $show, $settings_page_id )
        {
            if ( $settings_page_id === 'learndash_lms_advanced' && isset( $_GET['section-advanced'] ) && $_GET['section-advanced'] === 'settings_edd' ) {
                $show = false;
            }

            return $show;
        }
	}
}

add_action(
	'learndash_settings_sections_init',
	function() {
		Settings_Section::add_section_instance();
	}
);
