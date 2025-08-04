<?php
namespace LearnDash\Integrity;

/**
 * Class to prevent content copy.
 */
class Prevent_Content_Copy {
	/**
	 * Whether this feature is enabled or not
	 * @var bool
	 */
	private static $enabled;

	/**
	 * Init the hooks
	 * @return void
	 */
	public static function init() {
		$option = get_option( 'learndash_settings_ld_integrity' );
		if ( isset( $option['prevent_content_copy'] ) && 'yes' == $option['prevent_content_copy'] ) {
			self::$enabled = true;
		} else {
			self::$enabled = false;
		}

		if ( ! self::$enabled ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts used for this class feature
	 * @return void
	 */
	public static function enqueue_scripts() {
		$prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'prevent-content-copy', LEARNDASH_INTEGRITY_PLUGIN_URL . 'assets/js/prevent-content-copy' . $prefix . '.js', array( 'jquery' ), LEARNDASH_INTEGRITY_VERSION, true );
	}
}

Prevent_Content_Copy::init();
