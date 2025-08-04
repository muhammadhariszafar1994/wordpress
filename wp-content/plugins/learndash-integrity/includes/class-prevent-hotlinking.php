<?php
namespace LearnDash\Integrity;

/**
 * Class of prevent hotlinking feature.
 */
class Prevent_Hotlinking {
	/**
	 * .htaccess file
	 * @var string
	 */
	private static $htaccess_file = ABSPATH . '.htaccess';

	/**
	 * Init the hooks
	 * @return void
	 */
	public static function init() {
		add_action( 'update_option_learndash_settings_ld_integrity', array( __CLASS__, 'update_htaccess_on_update_option' ), 10, 3 );
	}

	/**
	 * Update .htaccess file on plugin activation
	 * @return void
	 */
	public static function update_htaccess_on_activation() {
		self::add_htaccess_rule();
	}

	/**
	 * Update .htaccess value when plugin option is updated
	 * @param  array  $old_value Old option value
	 * @param  array  $value     New option value
	 * @param  string $option    Option name
	 * @return void
	 */
	public static function update_htaccess_on_update_option( $old_value, $value, $option ) {
		if ( isset( $value['prevent_hotlinking'] ) && 'yes' == $value['prevent_hotlinking'] ) {
			self::add_htaccess_rule();
		} else {
			self::remove_htaccess_rule();
		}
	}

	/**
	 * Remove .htaccess rule on plugin uninstall
	 * @return void
	 */
	public static function update_htaccess_on_uninstall() {
		self::remove_htaccess_rule();
	}

	/**
	 * Add .htaccess rule to prevent hotlink
	 */
	public static function add_htaccess_rule() {
		$protected = apply_filters( 'learndash_integrity_protected_file_extensions', array(
			'jpg', 'jpeg', 'png', 'gif', 'avi', 'flv', 'wmv', 'mp4', 'mov', 'mp3'
		) );
		$protected = implode( '|', $protected );

		$home_url = get_home_url();
		$domain   = preg_replace( '/(https?:\/\/(?:www\.)?)(.*)/i', '$2', $home_url );

$rule = <<<RULE
### START LEARNDASH INTEGRITY ###
RewriteEngine on
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$domain [NC]
RewriteRule \.($protected)$ - [NC,F,L]
### END LEARNDASH INTEGRITY ###
RULE;

		$htaccess = '';
		if ( file_exists( self::$htaccess_file ) ) {
			$htaccess = file_get_contents( self::$htaccess_file );

			if ( mb_strpos( $htaccess, 'START LEARNDASH INTEGRITY' ) === false ) {
				$htaccess = $htaccess . "\n\n" . $rule;
			}
		} else {
			$htaccess = $rule;
		}

		file_put_contents( self::$htaccess_file, $htaccess );
	}

	/**
	 * Remove .htaccess rule to prevent hotlink
	 */
	public static function remove_htaccess_rule() {
		$htaccess = file_get_contents( self::$htaccess_file );
		$new_htaccess = preg_replace( '/\n*?### START LEARNDASH INTEGRITY.*END LEARNDASH INTEGRITY ###/s', '', $htaccess );

		file_put_contents( self::$htaccess_file, $new_htaccess );
	}
}

Prevent_Hotlinking::init();
