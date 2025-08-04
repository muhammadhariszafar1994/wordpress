<?php

namespace LearnDash\Integrity;

class reCaptcha_V3 extends ReCaptcha {

	/**
	 * @var string
	 */
	protected $setting_key = 'v3';

	protected $token_name = 'token';

	public function __construct() {
		parent::__construct();
		if ( true === $this->is_enabled() ) {
			$this->add_hooks();
		}
	}

	/**
	 * @param string $token A hash that return by google recaptcha
	 *
	 * @return mixed
	 */
	protected function verify_captcha( string $token ) {
		if ( ! empty( $this->last_result ) ) {
			if ( false === $this->last_result['success'] || $this->settings['score_threshold'] > $this->last_result['score'] ) {
				return false;
			}

			return true;
		}
		$url      = 'https://www.google.com/recaptcha/api/siteverify';
		$data     = array(
			'secret'   => $this->settings['secret_key'],
			'response' => $token
		);
		$response = wp_remote_post( $url, array(
			'body' => $data,
		) );
		$body     = wp_remote_retrieve_body( $response );
		$body              = json_decode( $body, true );

		$this->last_result = $body;
		if ( false === $body['success'] || $this->settings['score_threshold'] > $body['score'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Register the js script
	 */
	public function register_scripts() {
		wp_register_script( 'ld_recaptcha_v3',
							'https://www.google.com/recaptcha/api.js?render=' . $this->settings['site_key'],
							array(
								'jquery'
							) );
		wp_localize_script( 'ld_recaptcha_v3', 'LD_TP', array(
			'site_key' => $this->settings['site_key']
		) );
	}

	/**
	 * Load the script in the right place
	 */
	public function enqueue_captcha_script() {
		wp_enqueue_script( 'ld_recaptcha_v3' );
	}

	/**
	 * inject the recaptcha js code.
	 */
	public function add_recaptcha_script() {
		$el = array();
		if ( in_array( 'login', $this->location ) ) {
			$el[] = '#loginform';
		}
		if ( in_array( 'register', $this->location ) ) {
			$el[] = '#registerform, #setupform, #learndash_registerform';
		}
		if ( empty( $el ) ) {
			// should never here.
			return;
		}
		$el = implode( ',', $el );
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('<?php echo $el ?>').on('submit', function (e) {
					e.preventDefault()
					let that = $(this)
					grecaptcha.ready(function () {
						grecaptcha.execute(LD_TP.site_key, {action: 'submit'}).then(function (token) {
							that.prepend('<input type="hidden" name="token" value="' + token + '"/>')
							that.unbind('submit').submit()
						});
					});
				})
			})
		</script>
		<?php
	}
}

new reCaptcha_V3();
