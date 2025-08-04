<?php

namespace LearnDash\Integrity;

abstract class ReCaptcha {
	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $setting_key;

	/**
	 * @var string
	 */
	protected $token_name;

	/**
	 * Cache the last result from recaptcha, for preventing many call to registration_errors
	 * @var array
	 */
	protected $last_result;

	public function __construct() {
		$this->settings = get_option( 'learndash_settings_ld_integrity' );
		$this->location = $this->settings['location'] ?? array();
	}

	abstract public function register_scripts();

	abstract public function enqueue_captcha_script();

	abstract public function add_recaptcha_script();

	abstract protected function verify_captcha( string $token );

	/**
	 * Filter POST token data to always return string.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	private function filter_token(): string {
		$token = sanitize_text_field( wp_unslash( $_POST[ $this->token_name ] ) ) ?? '';

		// TODO: Add nonce check
		if ( ! is_string( $token ) ) {
			$token = '';
		}

		return $token;
	}

	/**
	 * Returns true if the login shortcode is found in a menu item description.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	function is_login_shortcode_found_in_menu(): bool {
		$login_shortcode_found_in_menu = false;

		foreach ( get_nav_menu_locations() as $term_id ) {
			if ( $login_shortcode_found_in_menu ) {
				break;
			}

			$menu_items = wp_get_nav_menu_items( $term_id );

			if ( is_array( $menu_items ) ) {
				foreach ( $menu_items as $menu_item ) {
					if ( has_shortcode( $menu_item->description, 'learndash_login' ) ) {
						$login_shortcode_found_in_menu = true;
						break;
					}
				}
			}
		}

		return $login_shortcode_found_in_menu;
	}

	protected function add_hooks() {
		add_action( 'wp_loaded', array( $this, 'register_scripts' ) );
		add_action( 'login_head', array( $this, 'enqueue_captcha_script' ) );
		add_action( 'login_footer', array( $this, 'add_recaptcha_script' ), 9999 );
		if ( is_multisite() ) {
			// enqueue the signup page for multisite
			add_action( 'after_signup_form', array( $this, 'add_recaptcha_script' ) );
			add_action( 'before_signup_form', array( $this, 'enqueue_captcha_script' ) );
		}
		// enqueue enqueue the script for ld form
		add_action( 'learndash_registration_form_fields_before', array( $this, 'enqueue_captcha_script' ) );
		add_action( 'learndash_registration_form_after', array( $this, 'add_recaptcha_script' ) );
		add_action( 'wp', function () {
			if ( ! is_admin() && ! is_user_logged_in() && function_exists( 'get_the_ID' ) ) {
				$id   = get_the_ID();
				$post = get_post( $id );

				// the login popup if possible
				if (
					is_object( $post )
					&& (
						'sfwd-courses' === $post->post_type
						|| has_shortcode( $post->post_content, 'learndash_login' )
						|| has_block( 'learndash/ld-login', $post->post_content )
						|| has_block( 'learndash/ld-registration', $post->post_content )
						|| $this->is_login_shortcode_found_in_menu()
					)
				) {
					// we should enqueue the script here for the login modal
					add_action( 'wp_head', array( $this, 'enqueue_captcha_script' ) );
					add_action( 'wp_footer', array( $this, 'add_recaptcha_script' ) );
				}
			}
		} );
		if ( in_array( 'login', $this->location ) ) {
			// do the verification when login
			add_filter( 'wp_authenticate_user', array( $this, 'verify_captcha_login' ), 9 );
		}
	}

	/**
	 * @param $result
	 *
	 * @return mixed
	 */
	public function verify_captcha_register_multi( $result ) {
		global $current_user;

		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! empty( $current_user->data->ID ) ) {
			return $result;
		}

		if ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) {
			$errors = $result['errors'];
		} else {
			$errors = new \WP_Error();
		}

		$token = $this->filter_token();

		if ( empty( $token ) ) {
			$errors->add(
				'captcha_error',
				__( '<strong>Error:</strong> Please complete the captcha', 'learndash-integrity' )
			);

			$result['errors'] = $errors;
		} elseif ( ! $this->verify_captcha( $token ) ) {
			$errors->add(
				'captcha_error',
				__( 'Nice try!', 'learndash-integrity' )
			);

			$result['errors'] = $errors;
		}

		return $result;
	}


	/**
	 * In multisite register
	 *
	 * @param $errors
	 */
	public function display_signup_recaptcha( $errors ) {
		if ( is_wp_error( $errors ) ) {
			$error_message = $errors->get_error_message( 'captcha_error' );
			if ( ! empty( $error_message ) ) {
				printf( '<p class="error">%s</p>', $error_message );
			}
		}
	}

	/**
	 * @param \WP_Error $errors
	 */
	public function verify_captcha_register( $errors ) {
		if ( defined( 'XMLRPC_REQUEST' ) ) {
			return $errors;
		}

		$token = $this->filter_token();

		if ( empty( $token ) ) {
			$errors->add(
				'captcha_error',
				__( 'Nice try!', 'learndash-integrity' )
			);
		} elseif ( false === $this->verify_captcha( $token ) ) {
			$errors->add(
				'captcha_error',
				__( 'Nice try!', 'learndash-integrity' )
			);
		}

		return $errors;
	}

	/**
	 * @param $user
	 *
	 * @return mixed|void
	 */
	public function verify_captcha_login( $user ) {
		if ( defined( 'XMLRPC_REQUEST' ) ) {
			return $user;
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$token = $this->filter_token();

			if ( empty( $token ) ) {
				if ( is_wp_error( $user ) ) {
					$user->add(
						'captcha_error',
						__( '<strong>Error:</strong> Please complete the captcha', 'learndash-integrity' )
					);
				} else {
					return new \WP_Error(
						'captcha_error',
						__( '<strong>Error:</strong> Please complete the captcha', 'learndash-integrity' )
					);
				}
			} elseif ( false === $this->verify_captcha( $token ) ) {
				if ( is_wp_error( $user ) ) {
					$user->add(
						'captcha_error',
						__( '<strong>Error:</strong> Nice try!', 'learndash-integrity' )
					);
				} else {
					return new \WP_Error(
						'captcha_error',
						__( '<strong>Error:</strong> Nice try!', 'learndash-integrity' )
					);
				}
			}
		}

		return $user;
	}

	public function register_captcha_error_ld_registration( $errors ) {
		$errors['captcha_error'] = __( 'Please complete the captcha', 'learndash-integrity' );

		return $errors;
	}

	/**
	 * Check if this is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		if (
			! isset( $this->settings['recaptcha'] )
			|| 'yes' !== $this->settings['recaptcha']
		) {
			return false;
		}

		if (
			! isset( $this->settings[ $this->setting_key ] )
			|| 'yes' !== $this->settings[ $this->setting_key ]
		) {
			return false;
		}

		if ( ! count( $this->location ) ) {
			return false;
		}

		return true;
	}
}
