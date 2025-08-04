<?php

namespace LearnDash\Integrity;

class reCaptcha_V2 extends ReCaptcha {
	/**
	 * @var string
	 */
	protected $setting_key = 'v2';

	protected $token_name = 'g-recaptcha-response';

	public function __construct() {
		parent::__construct();
		if ( true === $this->is_enabled() ) {
			$this->add_hooks();
			if ( in_array( 'login', $this->location ) ) {
				add_action( 'login_form', array( $this, 'show_captcha_checkbox' ) );
				add_filter( 'login_form_middle', array( $this, 'show_captcha_checkbox_inline' ) );
			}

			if ( in_array( 'register', $this->location ) ) {
				if ( ! is_multisite() ) {
					add_action( 'register_form', array( $this, 'show_captcha_checkbox' ) );
					add_action( 'learndash_register_form', array( $this, 'show_captcha_checkbox_inline' ) );
				}
			}
		}
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
		$this->show_captcha_checkbox();
	}

	/**
	 * @param string $token A hash that return by google recaptcha
	 *
	 * @return mixed
	 */
	protected function verify_captcha( string $token ) {
		if ( ! empty( $this->last_result ) ) {
			return $this->last_result['success'];
		}
		$url  = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret'   => $this->settings['secret_key_v2'],
			'response' => $token
		);
		$response = wp_remote_post( $url, array(
			'body' => $data,
		) );
		$body     = wp_remote_retrieve_body( $response );
		$body              = json_decode( $body, true );
		$this->last_result = $body;

		return $body['success'];
	}

	public function show_captcha_checkbox_inline( $content ) {
		$content .= '<div class="ld_captcha_el"></div>';

		return $content;
	}

	public function show_captcha_checkbox() {
		?>
		<div class="ld_captcha_el"></div>
		<?php
	}

	public function add_recaptcha_script() {
		?>
		<script type="text/javascript">
			var onloadCallback = function () {
				let els = document.getElementsByClassName('ld_captcha_el')
				Array.from(els).forEach(function (element) {
					grecaptcha.render(element, {
						'sitekey': '<?php echo $this->settings['site_key_v2'] ?>'
					});
				});
			};
		</script>
		<?php
	}

	public function register_scripts() {
	}

	public function enqueue_captcha_script() {
		?>
		<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
				async defer>
		</script>
		<?php
	}
}

new reCaptcha_V2();
