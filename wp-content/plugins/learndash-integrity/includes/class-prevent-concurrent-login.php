<?php
/**
 * Handles prevent concurrent login functionality
 */

namespace LearnDash\Integrity;

/**
 * Class to prevent concurrent login.
 */
class Prevent_Concurrent_Login {
	/**
	 * Whether this feature is enabled or not
	 *
	 * @var bool
	 */
	private static $enabled;

	/**
	 * Init the hooks
	 *
	 * @return void
	 */
	public static function init() {

		$option = get_option( 'learndash_settings_ld_integrity' );

		if ( isset( $option['prevent_concurrent_login'] ) && 'yes' == $option['prevent_concurrent_login'] ) {
			self::$enabled = true;
		} else {
			self::$enabled = false;
		}

		if ( ! self::$enabled ) {
			return;
		}

		add_filter( 'wp_login_errors', array( __CLASS__, 'login_errors' ) );
		add_action( 'wp_login', array( __CLASS__, 'save_login_transient_on_user_login' ), 10, 2 );
		add_action( 'show_user_profile', array( __CLASS__, 'learndash_integrity_concurrent_login_profile_field' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'learndash_integrity_concurrent_login_profile_field' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_profile' ), 1 );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_profile' ), 1 );
		add_action( 'wp_logout', array( __CLASS__, 'remove_login_transient_on_user_logout' ) );
		add_action( 'deleted_user_meta', array( __CLASS__, 'destroy_user_sessions' ), 10, 4 );
	}

	/**
	 * Filter login error messages on login form
	 *
	 * @param object $errors WP_Error Object.
	 *
	 * @return object $error WP_Error Object
	 */
	public static function login_errors( $errors ) {
		if ( isset( $_GET['exceed_max_concurrent_login'] ) ) {
			$errors->add( 'exceed_max_concurrent_login', __( 'Your account has exceeded the maximum concurrent logins.', 'learndash-integrity' ), 'error' );
		}
		return $errors;
	}

	/**
	 * Save login transient data on user login
	 *
	 * @param  string $user_login User's username.
	 * @param  object $user       WP_User object.
	 * @return void
	 */
	public static function save_login_transient_on_user_login( $user_login, $user ) {
		$setting_option = get_option( 'learndash_settings_ld_integrity' );

		if ( ! isset( $setting_option['prevent_concurrent_login_exclude_roles'] ) ) {
			$setting_option['prevent_concurrent_login_exclude_roles'] = '';
		}

		$excluded_roles = $setting_option['prevent_concurrent_login_exclude_roles'];

		// User role exclusion.
		$exclude_user_roles = ( isset( $excluded_roles ) && ! empty( $excluded_roles ) ? $excluded_roles : array() );
		$current_user_roles = $user->roles;

		$role_check = false;

		$role_check = ( array_intersect( $exclude_user_roles, $current_user_roles ) ? true : false );

		// If current user roles match exclude list, allow login.
		if ( true === $role_check ) {
			return;
		}

		if ( ! self::is_login_quota_available( $user->ID ) ) {
			wp_logout();
			$url = add_query_arg( array( 'exceed_max_concurrent_login' => '' ), wp_login_url() );
			wp_safe_redirect( $url );
			exit();
		}

		$timestamp = time();

		setcookie( 'learndash_login_timestamp', $timestamp, $timestamp + HOUR_IN_SECONDS );
		set_transient( 'learndash_user_login_' . $user->ID, $timestamp, HOUR_IN_SECONDS );
	}

	/**
	 * Remove user login transient on user logout.
	 *
	 * @param  int $user_id WP_User ID.
	 * @return void
	 */
	public static function remove_login_transient_on_user_logout( $user_id ) {
		if ( isset( $_COOKIE['learndash_login_timestamp'] ) && ! empty( $_COOKIE['learndash_login_timestamp'] ) ) {
			delete_transient( 'learndash_user_login_' . $user_id );
			setcookie( 'learndash_login_timestamp', 0, time() - HOUR_IN_SECONDS );
		}
	}

	/**
	 * Check if login bypass transient is set.
	 *
	 * @since 1.1
	 *
	 * @param  int $user_id WP_User ID.
	 * @return boolean True if transient is empty|false otherwise
	 */
	public static function learndash_integrity_get_user_login_transient( $user_id ) {
		$transient = get_transient( 'learndash_user_login_' . $user_id );
		if ( empty( $transient ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if login quota is available
	 *
	 * @param  int $user_id WP_User ID.
	 * @return boolean True if available|false otherwise
	 */
	public static function is_login_quota_available( $user_id ) {
		$transient    = self::learndash_integrity_get_user_login_transient( $user_id );
		$login_bypass = self::learndash_integrity_get_user_login_bypass( $user_id );
		$cookie_timestamp    = isset( $_COOKIE['learndash_login_timestamp'] ) ? intval( $_COOKIE['learndash_login_timestamp'] ) : false;
		$transient_timestamp = intval( get_transient( 'learndash_user_login_' . $user_id ) );

		if ( $login_bypass || $transient || ( ! $transient && $cookie_timestamp === $transient_timestamp ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if user is allowed to bypass login block
	 *
	 * @since 1.1
	 *
	 * @param int $user_id WP_User ID.
	 * @return string $bypass_user_lockout Whether or not the user can bypass the login block.
	 */
	public static function learndash_integrity_get_user_login_bypass( $user_id ) {
		$bypass_user_lockout = get_user_meta( $user_id, 'learndash_integrity_bypass_concurrent_login', true );

		return $bypass_user_lockout;
	}

	/**
	 * Checkbox display in WP admin user profile
	 *
	 * @since 1.1
	 *
	 * @param WP_User $user WP_User Object.
	 */
	public static function learndash_integrity_concurrent_login_profile_field( $user ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return '';
		}
		$user_login_bypass = get_user_meta( $user->ID, 'learndash_integrity_bypass_concurrent_login', true );
		?>
			<div id="learndash_integrity_user_bypass">
			<h2>
				<?php
					echo esc_html__( 'Bypass Concurrent Login Lockout ( LearnDash Integrity )', 'learndash-integrity' );
				?>
				</h2>
				<p><input type="checkbox" id="learndash_int_bypass_concurrent_login" name="learndash_int_bypass_concurrent_login" <?php echo ( 'on' === $user_login_bypass ? 'checked' : '' ); ?>>
				<label for="learndash_int_bypass_concurrent_login">
				<?php
					echo esc_html__( 'Prevent this user from being locked out when attempting to log into concurrent sessions.', 'learndash-integrity' );
				?>
				</label></p>
		<?php
	}

	/**
	 * Save WP User Profile hook.
	 *
	 * @since 1.1
	 *
	 * @param int $user_id ID of user being saved.
	 */
	public static function save_user_profile( $user_id ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		if ( empty( $user_id ) ) {
			return;
		}

		if ( isset( $_POST['learndash_int_bypass_concurrent_login'] ) ) {
			update_user_meta( $user_id, 'learndash_integrity_bypass_concurrent_login', ( sanitize_text_field( $_POST['learndash_int_bypass_concurrent_login'] ) ) );
		} else {
			update_user_meta( $user_id, 'learndash_integrity_bypass_concurrent_login', '' );
		}
	}

	/**
	 * Destroy user LD login transient if user login sessions are destroyed.
	 * 
	 * @since 1.1
	 *
	 * @param string[] $meta_ids    An array of metadata entry IDs to delete.
	 * @param int      $user_id     ID of the user metadata is for.
	 * @param string   $meta_key    Metadata key.
	 * @param mixed    $meta_value Metadata value.
	 * @return void
	 */
	public static function destroy_user_sessions( $meta_ids, $user_id, $meta_key, $meta_value ) {
		if ( $meta_key === 'session_tokens' ) {
			delete_transient( 'learndash_user_login_' . $user_id );
		}
	}
}

Prevent_Concurrent_Login::init();
