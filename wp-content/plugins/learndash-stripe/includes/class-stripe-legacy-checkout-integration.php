<?php
/**
* Stripe legacy checkout integration class
*/
class LearnDash_Stripe_Legacy_Checkout_Integration extends LearnDash_Stripe_Integration_Base {
	/**
	 * Class construction function
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'process_checkout' ) );
	}

	/**
	 * Process stripe checkout
	 */
	public function process_checkout() {
		$transaction_status = array();
		$transaction_status['stripe_message_type'] 	= '';
		$transaction_status['stripe_message'] 		= '';

		//error_log('in '. __FUNCTION__ ."\r\n", 3, ABSPATH .'/ld_debug.log');
		//error_log('_POST<pre>'. print_r($_POST, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'ld_stripe_init_checkout' ) {
			$this->config();

			if ( ( isset( $_POST['stripe_token_id'] ) ) && ( !empty( $_POST['stripe_token_id'] ) ) ) {
				$token_id = sanitize_text_field( $_POST['stripe_token_id'] );
			} else {
				$transaction_status['stripe_message_type'] = 'error';
				$transaction_status['stripe_message'] = __( 'No token found. Please activate javascript to make a purchase.', 'learndash-stripe' );
				$this->show_notification( $transaction_status );
			}
			$transaction_status['token_id'] = $token_id;


			if ( isset( $_POST['stripe_token_email'] ) ) {
				$token_email = sanitize_text_field( $_POST['stripe_token_email'] );
			} else {
				$token_email = '';
			}
			$transaction_status['token_email'] = $token_email;

			if ( ( isset( $_POST['stripe_course_id'] ) ) && (!empty( $_POST['stripe_course_id'] ) ) ) {
				$course_id   = sanitize_text_field( $_POST['stripe_course_id'] );
			} else {
				$course_id = 0;
			}

			if ( empty( $course_id ) ) {
				return;
			}

			$transaction_status['course_id'] = $course_id;

			if ( ! $this->is_transaction_legit() ) {
				$transaction_status['stripe_message_type'] = 'error';
				$transaction_status['stripe_message'] = __( 'The course form data doesn\'t match with the official course data. Cheatin\' huh?', 'learndash-stripe' );
				$this->show_notification( $transaction_status );
			}

			if ( is_user_logged_in() ) {
				$user_id     = get_current_user_id();
				$customer_id = get_user_meta( $user_id, $this->stripe_customer_id_meta_key, true );
				$customer_id = $this->add_stripe_customer( $user_id, $customer_id, $token_email, $token_id );
				$user        = wp_get_current_user();
			} else {
				// Needed a flag so we know at the end of this was a new user vs existing user so we can return the correct message.
				// The problem was at the end if this is an existing user there is no email. So the message was incorrect.
				$is_new_user = false;

				$user = get_user_by( 'email', $token_email );

				if ( false === $user ) {
					// Call Stripe API first so user acccount won't be created if there's error
					$customer_id = $this->add_stripe_customer( false, false, $token_email, $token_id );

					$password = wp_generate_password( 18, true, false );
					$new_user = $this->create_user( $token_email, $password, $token_email );

					if ( ! is_wp_error( $new_user ) ) {
						$user_id     = $new_user;
						$user        = get_user_by( 'ID', $user_id );

						update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );

						// Need to allow for older versions of WP.
						global $wp_version;
						if (version_compare($wp_version, '4.3.0', '<')) {
						    wp_new_user_notification( $user_id, $password );
						} else if (version_compare($wp_version, '4.3.0', '==')) {
						    wp_new_user_notification( $user_id, 'both' );
						} else if (version_compare($wp_version, '4.3.1', '>=')) {
						    wp_new_user_notification( $user_id, null, 'both' );
						}
						$is_new_user = true;

					} else {
						$error_code = $new_user->get_error_code();
						$transaction_status['stripe_message_type'] = 'error';
						$transaction_status['stripe_message'] = __( 'Failed to create a new user account. Please try again. Reason: ', 'learndash-stripe' ) . $new_user->get_error_message( $error_code );
						$this->show_notification( $transaction_status );
					}

				} else {
					$user_id = $user->ID;
					$customer_id = get_user_meta( $user_id, $this->stripe_customer_id_meta_key, true );
					$customer_id = $this->add_stripe_customer( $user_id, $customer_id, $token_email, $token_id );
				}
			}

			$site_name = get_bloginfo( 'name' );
			$metadata = [
				'user_id' => $user_id,
				'course_id' => $course_id,
			];

			if ( 'paynow' == $_POST['stripe_price_type'] ) {
				try {
					$charge = \Stripe\Charge::create( array(
						'amount'   => sanitize_text_field( $_POST['stripe_price'] ),
						'currency' => sanitize_text_field( strtolower( $_POST['stripe_currency'] ) ),
						'customer' => $customer_id,
						'description' => sprintf( '%s: %s', $site_name, stripslashes( sanitize_text_field( $_POST['stripe_name'] ) ) ),
						'receipt_email' => $user->user_email,
					) );

					add_user_meta( $user_id, 'stripe_charge_id', $charge->id, false );

				} catch ( \Stripe\Error\Card $e ) {
					// Card is declined
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\RateLimit $e ) {
					// Too many requests made to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\InvalidRequest $e ) {
					// Invalid parameters suplied to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Authetication $e ) {
					// Authentication failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\ApiConnection $e ) {
					// Network communication with Stripe failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Base $e ) {
					// Generic error
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( Exception $e ) {
					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $e->getMessage() . '. ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );
				}

			} elseif ( 'subscribe' == $_POST['stripe_price_type'] ) {
				$course_args = $this->get_course_args( $course_id );
				extract( $course_args );

				if ( empty( $course_interval ) || empty( $course_interval_count ) || empty( $course_price ) ) {
					return;
				}

				$plan_id = get_post_meta( $course_id, 'stripe_plan_id', false );
				$plan_id = end( $plan_id );

				if ( ! empty( $plan_id ) ) {
					try {
						$plan = \Stripe\Plan::retrieve( array(
							'id'     => $plan_id,
							'expand' => array( 'product' ),
						) );
						// error_log('plan<pre>'. print_r($plan, true) .'</pre>'."\r\n", 3, ABSPATH .'/ld_debug.log');

						if (
							( isset( $plan ) && is_object( $plan ) ) &&
							$plan->amount         != $course_price ||
							$plan->currency       != strtolower( $currency ) ||
							$plan->id             != $plan_id ||
							$plan->interval       != $course_interval ||
							htmlspecialchars_decode( $plan->product->name )           != stripslashes( sanitize_text_field( $course_name ) ) ||
							$plan->interval_count != $course_interval_count
						) {
							// Don't delete the old plan as old subscription may
							// still attached to it

							// Create a new plan
							$plan = \Stripe\Plan::create( array(
								// Required
								'amount'   => esc_attr( $course_price ),
								'currency' => strtolower( $currency ),
								'id'       => $course_plan_id . '-' . $this->generate_random_string( 5 ),
								'interval' => $course_interval,
								'product'  => array(
									'name'     => stripslashes( sanitize_text_field( $course_name ) ),
								),
								// Optional
								'interval_count' => esc_attr( $course_interval_count ),
							) );

							$plan_id = $plan->id;

							add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
						}
					} catch ( Exception $e ) {
						// Create a new plan
						$plan = \Stripe\Plan::create( array(
							// Required
							'amount'   => esc_attr( $course_price ),
							'currency' => strtolower( $currency ),
							'id'       => $course_plan_id . '-' . $this->generate_random_string( 5 ),
							'interval' => $course_interval,
							'product'  => array(
								'name' => stripslashes( sanitize_text_field( $course_name ) ),
							),
							// Optional
							'interval_count' => esc_attr( $course_interval_count ),
						) );

						$plan_id = $plan->id;

						add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
					}
				} else {
					// Create a new plan
					$plan = \Stripe\Plan::create( array(
						// Required
						'amount'   => esc_attr( $course_price ),
						'currency' => strtolower( $currency ),
						'id'       => $course_plan_id,
						'interval' => $course_interval,
						'product'  => array(
							'name' => stripslashes( sanitize_text_field( $course_name ) ),
						),
						// Optional
						'interval_count' => esc_attr( $course_interval_count ),
					) );

					$plan_id = $plan->id;

					add_post_meta( $course_id, 'stripe_plan_id', $plan_id, false );
				}

				$trial_period_days = null;
				if ( ! empty( $course_trial_interval_count ) && ! empty( $course_trial_interval ) ) {
					switch ( $course_trial_interval ) {
						case 'day':
							$trial_period_days = $course_trial_interval_count * 1;
							break;

						case 'week':
							$trial_period_days = $course_trial_interval_count * 7;
							break;

						case 'month':
							$trial_period_days = $course_trial_interval_count * 30;
							break;

						case 'year':
							$trial_period_days = $course_trial_interval_count * 365;
							break;
					}
				}

				if ( ! empty( $trial_period_days ) ) {
					if ( ! empty( $course_trial_price ) ) {
						$trial_product = $this->stripe->products->create( [
							'name' => sprintf( _n( '%d Day Trial', '%d Days Trial', $trial_period_days, 'learndash-course-grid' ), $trial_period_days ),
						] );

						$line_items = [
							[
								'price_data' => [
									'currency' => $currency,
									'product' => $trial_product->id,
									// 'name' => sprintf( _n( '%d Day Trial', '%d Days Trial', $trial_period_days, 'learndash-course-grid' ), $trial_period_days ),
									// 'product' => '',
									'unit_amount' => $course_trial_price,
								],
								'quantity' => 1,
							]
						];
					} else {
						$line_items = null;
					}

					$metadata['has_trial'] = true;
				} else {
					$line_items = null;
				}

				if ( ! empty( $course_recurring_times ) ) {
					$metadata['has_recurring_limit'] = true;
				}

				$subscription_data = array(
					'add_invoice_items' => $line_items,
					'customer' => $customer_id,
					'metadata' => $metadata,
					'items' => array( array(
						'plan' => $plan_id
					) ),
					'trial_period_days' => $trial_period_days,
				);

				try {
					$subscription = \Stripe\Subscription::create( $subscription_data );

					// Bail if susbscription is not active
					if ( empty( $subscription->id ) ) {
						$transaction_status['stripe_message_type'] = 'error';
						$transaction_status['stripe_message'] = __( 'Failed to create a subscription. Please check your card and try it again later.', 'learndash-stripe' );
						$this->show_notification( $transaction_status );
					}

					if ( ! empty( $trial_product->id ) )  {
						$this->stripe->products->update(
							$trial_product->id,
							[
								'active' => false,
							]
						);
					}

					add_user_meta( $user_id, 'stripe_subscription_id', $subscription->id, false );

					add_user_meta( $user_id, 'stripe_plan_id', $plan_id, false );

				} catch ( \Stripe\Error\Card $e ) {
					// Card is declined
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\RateLimit $e ) {
					// Too many requests made to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'];
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\InvalidRequest $e ) {
					// Invalid parameters suplied to the API
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Authetication $e ) {
					// Authentication failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please contact website administrator.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\ApiConnection $e ) {
					// Network communication with Stripe failed
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( \Stripe\Error\Base $e ) {
					// Generic error
					$body  = $e->getJsonBody();
					$error = $body['error'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error['message'] . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );

				} catch ( Exception $e ) {
					// Generic error
					$body  = $e->getJsonBody();
					$error = $body['error']['message'];

					$transaction_status['stripe_message_type'] = 'error';
					$transaction_status['stripe_message'] = $error . ' ' . __( 'Please try again later.', 'learndash-stripe' );
					$this->show_notification( $transaction_status );
				}
			}

			// If charge or subscription is successful

			// Associate course with user
			$this->add_course_access( $course_id, $user_id );

			$transaction = $_POST;

			// Log transaction
			$this->record_transaction( $transaction, $course_id, $user_id, $token_email );

			$success_url = $this->get_success_url( $course_id );
			if ( ! empty( $success_url ) ) {
				wp_redirect( $success_url );
				exit();
			}

			// Fall through to this if there is not a valid redirect URL. Again I hate using sessions for this. Not time to rewrite all this logic for now.
			if ( $is_new_user == true ) {
				$transaction_status['stripe_message'] = __( 'The transaction was successful. Please check your email and log in to access the course.', 'learndash-stripe' );
			} else {
				if ( is_user_logged_in() ) {
					$transaction_status['stripe_message'] = __( 'The transaction was successful. You now have access the course.', 'learndash-stripe' );
				} else {
					$transaction_status['stripe_message'] = __( 'The transaction was successful. Please log in to access the course.', 'learndash-stripe' );
				}
			}
			$this->show_notification( $transaction_status );
		}
	}

	/**
	 * Display error message to users
	 * @param  array  $transaction_status Transaction status
	 * @return void
	 */
	function show_notification( $transaction_status = array() ) {
		$unique_id = wp_generate_password( 10, false, false );
		$transient_id = 'ld_'. $unique_id;

		set_transient( $transient_id, $transaction_status, HOUR_IN_SECONDS );

		$redirect_url = add_query_arg( 'ld-trans-id', $unique_id );
		wp_redirect( $redirect_url );
		exit();
	}

	/**
	 * Record transaction in database
	 *
	 * @param  array  $transaction_data Transaction data passed through $_POST
	 * @param  int    $course_id        Post ID of a course
	 * @param  int    $user_id          ID of a user
	 * @param  string $user_email       Email of the user
	 */
	public function record_transaction( $transaction_data, $course_id, $user_id, $user_email ) {
		// ld_debug( 'Starting Transaction Creation.' );

		$transaction_data['user_id']      = $user_id;
		$transaction_data['course_id']    = $course_id;
		$transaction_data['post_id']      = $course_id;
		$transaction_data['stripe_price'] = ! $this->is_zero_decimal_currency( $transaction_data['stripe_currency'] ) && $transaction_data['stripe_price'] > 0
			? number_format( $transaction_data['stripe_price'] / 100, 2, '.', '' )
			: $transaction_data['stripe_price'];

		$course_title = $_POST['stripe_name'];

		// ld_debug( 'Course Title: ' . $course_title );

		$transaction_id = wp_insert_post( array( 'post_title' => "Course {$course_title} Purchased By {$user_email}", 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );

		// ld_debug( 'Created Transaction. Post Id: ' . $post_id );

		if ( $transaction_id > 0 && ! ( $transaction_id instanceof WP_Error ) ) {
			foreach ( $transaction_data as $key => $value ) {
				update_post_meta( $transaction_id, $key, $value );
			}

            do_action( 'learndash_transaction_created', $transaction_id );
		}
	}

	/**
	 * Output button scripts
	 * @return void
	 */
	public function button_scripts() {
		?>
		<script src="https://checkout.stripe.com/checkout.js"></script>
		<script type="text/javascript">
            var Stripe_Handler;
            var ld_init_stripe_legacy = function() {
                jQuery( '.learndash_stripe_button form.learndash-stripe-checkout input.learndash-stripe-checkout-button' ).each( function() {
                    var parent_form = jQuery( this ).parent( 'form.learndash-stripe-checkout' );
                    Stripe_Handler.init( parent_form );
                });
            };

			jQuery( document ).ready( function( $ ) {
				Stripe_Handler = {
					init: function( form_ref ) {
						var price = $( 'input[name="stripe_price"]', form_ref ).val(),
							price_type = $( 'input[name="stripe_price_type"]', form_ref ).val();

						if ( 'subscribe' == price_type ) {
							var trial_price = $( 'input[name="stripe_trial_price"]', form_ref ).val(),
								trial_interval_count = $( 'input[name="stripe_trial_interval_count"]', form_ref ).val();

							if ( trial_interval_count > 0 ) {
								if ( trial_price > 0 ) {
									price = trial_price;
								} else {
									price = 0;
								}
							}
						}

						var handler = StripeCheckout.configure({
							key         : '<?php echo $this->get_publishable_key() ?>',
							amount      : parseInt( price ),
							currency    : $( 'input[name="stripe_currency"]', form_ref ).val(),
							description : $( 'input[name="stripe_name"]', form_ref ).val(),
							email       : $( 'input[name="stripe_email"]', form_ref ).val(),
							locale      : 'auto',
							name        : '<?php echo get_bloginfo( 'name', 'raw' ) ?>',
							token: function(token) {
								// Use the token to create the charge with a server-side script.
								// You can access the token ID with `token.id`
								var stripe_token_id = $( '<input type="hidden" name="stripe_token_id" />', form_ref ).val( token.id );
								var stripe_token_email = $( '<input type="hidden" name="stripe_token_email" />', form_ref ).val( token.email );
								$( form_ref ).append( stripe_token_id );
								$( form_ref ).append( stripe_token_email );
								$( form_ref ).submit();
							}
						});

						$( 'input.learndash-stripe-checkout-button', form_ref ).on( 'click', function(e) {
                            // Unbind previous handlers
                            $( this ).unbind( 'click' );

                            // Open Checkout with further options
							handler.open({});
							e.preventDefault();
						});

						// Close Checkout on page navigation
						$( window ).on( 'popstate', function() {
							handler.close();
						} );
					}
				};

                ld_init_stripe_legacy();
			});
		</script>
		<?php
	}

	/**
	 * Add Customer to Stripe
	 * @param int    $user_id     ID of a user
	 * @param int    $customer_id Stripe customer ID
	 * @param string $token_email Email of a user, got from token
	 * @param string $token_id    Token ID
	 * @return string Stripe customer ID
	 */
	public function add_stripe_customer( $user_id, $customer_id, $token_email, $token_id ) {
		$this->config();

		if ( ! empty( $customer_id ) && ! empty( $user_id ) ) {
			try {
				$customer = \Stripe\Customer::retrieve( $customer_id );
			} catch ( Exception $e ) {
				$customer = null;
				error_log( 'Error retrieving user when adding customer for Legacy Checkout: ' . print_r( $e->getMessage(), true ) );
			}

			if ( ! isset( $customer ) || ( isset( $customer->deleted ) && $customer->deleted ) ) {
				$customer = \Stripe\Customer::create( array(
					'email'  => $token_email,
					'source' => $token_id,
				) );
			}

			$customer_id = $customer->id;

			update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );
		} else {
			try {
				$customer = \Stripe\Customer::create( array(
					'email'  => $token_email,
					'source' => $token_id,
				) );

				$customer_id = $customer->id;

				if ( ! empty( $user_id ) ) {
					update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );
				}
			} catch ( Exception $e ) {
				$body  = $e->getMessage();
				error_log( 'Error: ' . print_r( $body, true ) );
				$error = $body['error'] ?? '';

				$transaction_status['stripe_message_type'] = 'error';
				$transaction_status['stripe_message'] = $error['message'] ?? __( 'Unknown error. Please contact site administrator to check the site log.' , 'learndash-stripe' );
				$this->show_notification( $transaction_status );
			}
		}

		return $customer_id;
	}

	/**
	 * Output Stripe error alert
	 */
	public function output_transaction_message() {
		//if ( !is_singular( 'sfwd-courses' ) ) return;

		if ( ( isset( $_GET['ld-trans-id'] ) ) && ( ! empty( $_GET['ld-trans-id'] ) ) ) {

			//$queried_object = get_queried_object();
			//error_log('queried_object<pre>'. print_r($queried_object, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');

			//$transient_id = 'ld_'. $queried_object->ID .'_'. $_GET['ld-trans-id'];
			$transient_id = 'ld_'. $_GET['ld-trans-id'];
			//error_log('transient_id['. $transient_id .']' ."\r\n", 3, ABSPATH .'/ld_debug.log');

			$transaction_status = get_transient( $transient_id );
			//error_log('transaction_status<pre>'. print_r($transaction_status, true) .'</pre>' ."\r\n", 3, ABSPATH .'/ld_debug.log');

			delete_transient( $transient_id );
			if ( ! empty( $transaction_status ) ) {
				if ( ( isset( $transaction_status['stripe_message'] ) ) && ( !empty( $transaction_status['stripe_message'] ) ) && ( isset( $transaction_status['stripe_message_type'] ) ) ) {

					if ( $transaction_status['stripe_message_type'] == 'error' ) {
						?>
						<script type="text/javascript">
						jQuery( document ).ready( function() {
							if ( jQuery( '.learndash_checkout_buttons').length ) {
								jQuery( '<p class="learndash-error"><?php echo htmlentities( $transaction_status['stripe_message'], ENT_QUOTES ) ?></p>' ).insertAfter( '.learndash_checkout_buttons' );
							} else if ( jQuery( '#learndash_course_content' ).length ) {
								jQuery( '<p class="learndash-error"><?php echo htmlentities( $transaction_status['stripe_message'], ENT_QUOTES ) ?></p>' ).insertBefore( '#learndash_course_content' );
							}
						});
						</script>
						<?php
					} else if ( $transaction_status['stripe_message_type'] != 'error' ) {
						?>
						<script type="text/javascript">
						jQuery( document ).ready( function() {
							if ( jQuery('#learndash_course_content').length ) {
								jQuery( '<p class="learndash-success"><?php echo htmlentities( $transaction_status['stripe_message'], ENT_QUOTES ) ?></p>' ).insertBefore( '#learndash_course_content' );
							}
						});
						</script>
						<?php
					}
				}
			}
		}
	}
}

global $ld_stripe_legacy_checkout;
$ld_stripe_legacy_checkout = new LearnDash_Stripe_Legacy_Checkout_Integration();
