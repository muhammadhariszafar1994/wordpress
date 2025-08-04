<?php

/**
* Stripe checkout integration class
*/
class LearnDash_Stripe_Checkout_Integration extends LearnDash_Stripe_Integration_Base {
    /**
     * Class construction function
     */
    public function __construct() {
        parent::__construct();

        add_action( 'wp_ajax_nopriv_ld_stripe_init_checkout', array( $this, 'ajax_init_checkout' ) );
        add_action( 'wp_ajax_ld_stripe_init_checkout', array( $this, 'ajax_init_checkout' ) );
    }

    /**
     * Set Stripe checkout session on course page
     *
     * @return void
     */
    public function set_session( $course_id = null ) {
        $args = $this->get_course_args( $course_id );

        extract( $args );

        $this->config();

        $stripe_customer_id = get_user_meta( get_current_user_id(), $this->stripe_customer_id_meta_key, true );

        $stripe_customer = null;
        try {
            $stripe_customer = ! empty( $stripe_customer_id ) ? \Stripe\Customer::retrieve( $stripe_customer_id ) : null;
        } catch ( Exception $e ) {
            error_log( 'Error retrieving user when setting up session: ' . print_r( $e->getMessage(), true ) );
        }

        $customer = ! empty( $stripe_customer->id ) && empty( $stripe_customer->deleted ) && ! empty( $stripe_customer_id ) ? $stripe_customer_id : null;

        $user_id    = is_user_logged_in() ? get_current_user_id() : null;
        $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : null;

        $success_url = $this->get_success_url( $course_id );
        $success_url = add_query_arg( array(
            'ld_stripe'  => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ), $success_url );
        $cancel_url = $this->get_cancel_url( $course_id );

        $course_images = ! empty( $course_image ) ? array( $course_image ) : null;
        $metadata = array(
            'course_id' => $course_id,
            'user_id'   => $user_id,
        );

        $line_items = array( array(
            'name' => $course_name,
            'images' => $course_images,
            'amount' => $course_price,
            'currency' => $currency,
            'quantity' => 1,
        ) );

        $payment_intent_data = null;
        if ( 'paynow' === $course_price_type ) {
            $payment_intent_data = array(
                'receipt_email' => $user_email,
                'metadata' => $metadata,
            );
        }

        $subscription_data = null;
        if ( 'subscribe' === $course_price_type ) {
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
                    $line_items = [
                        [
                            'name' => sprintf( _n( '%d Day Trial', '%d Days Trial', $trial_period_days, 'learndash-course-grid' ), $trial_period_days ),
                            'amount' => $course_trial_price,
                            'currency' => $currency,
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
                'metadata' => $metadata,
                'items' => array( array(
                    'plan' => $plan_id
                ) ),
                'trial_period_days' => $trial_period_days,
            );
        }

        $session_args = apply_filters( 'learndash_stripe_session_args',
            array(
                'allow_promotion_codes' => true,
                'customer'              => $customer,
                'payment_method_types'  => $this->get_payment_methods(),
                'line_items'            => $line_items,
                'metadata'              => $metadata,
                'success_url'           => $success_url,
                'cancel_url'            => $cancel_url,
                'payment_intent_data'   => $payment_intent_data,
                'subscription_data'     => $subscription_data,
            )
        );

        try {
            $session = \Stripe\Checkout\Session::create( $session_args );
        } catch ( Exception $e ) {
            error_log( $e );
            return $e->getMessage();
        }

        if ( is_object( $session ) && is_a( $session, 'Stripe\Checkout\Session' ) ) {
            $this->session_id = $session->id;
            setcookie( 'ld_stripe_session_' . $course_id, $this->session_id, time() + DAY_IN_SECONDS );
            return $this->session_id;
        }
    }

    /**
     * AJAX function handler for init checkout
     * @uses ld_stripe_init_checkout WP AJAX action string
     * @return void
     */
    public function ajax_init_checkout() {
        error_reporting( E_ALL );
        ini_set( 'log_errors', 1 );

        if ( ! $this->is_transaction_legit() ) {
            wp_die( __( 'Cheatin\' huh?', 'learndash-stripe' ) );
        }

        $course_id  = intval( $_POST['stripe_course_id'] );
        $session_id = $this->set_session( $course_id );

        if ( ! isset( $session_id['error'] ) ) {
            echo json_encode( [ 'status' => 'success', 'session_id' => $session_id ] );
        } else {
            echo json_encode( ['status' => 'error', 'payload' => $session_id['error'] ] );
        }

        wp_die();
    }

    /**
     * Integration button scripts
     * @return void
     */
    public function button_scripts() {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.0-rc.1/dist/js.cookie.min.js"></script>
        <script src="https://js.stripe.com/v3/"></script>
        <script type="text/javascript">
            "use strict";

            function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

            function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

            function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

            var LD_Cookies = Cookies.noConflict();

            jQuery(document).ready(function ($) {
                var stripe = Stripe('<?php echo $this->publishable_key ?>');
                var ld_stripe_ajaxurl = <?php echo json_encode( admin_url( 'admin-ajax.php' ) ) ?>;

                $(document).on('submit', '.learndash-stripe-checkout', function (e) {
                    e.preventDefault();
                    var inputs = $(this).serializeArray();
                    inputs = inputs.reduce(function (new_inputs, value, index, inputs) {
                        new_inputs[value.name] = value.value;
                        return new_inputs;
                    }, {});

                    var ld_stripe_session_id = LD_Cookies.get('ld_stripe_session_id_' + inputs.stripe_course_id);

                    if (typeof ld_stripe_session_id != 'undefined') {
                        stripe.redirectToCheckout({
                            sessionId: ld_stripe_session_id
                        }).then(function (result) {
                            if (result.error.message.length > 0) {
                                alert(result.error.message);
                            }
                        });
                    } else {
                        $('.checkout-dropdown-button').hide();
                        $(this).closest('.learndash_checkout_buttons').addClass('ld-loading');
                        $('head').append('<style class="ld-stripe-css">' + '.ld-loading::after { background: none !important; }' + '.ld-loading::before { width: 30px !important; height: 30px !important; left: 53% !important; top: 62% !important; }' + '</style>');
                        $('.learndash_checkout_buttons .learndash_checkout_button').css({
                            backgroundColor: 'rgba(182, 182, 182, 0.1)'
                        });

                        // Set Stripe session
                        $.ajax({
                            url: ld_stripe_ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: _objectSpread({}, inputs)
                        }).done(function (response) {
                            if (response.status === 'success') {
                                LD_Cookies.set('ld_stripe_session_id_' + inputs.stripe_course_id, response.session_id); // If session is created

                                stripe.redirectToCheckout({
                                    sessionId: response.session_id
                                }).then(function (result) {
                                    if (result.error.message.length > 0) {
                                        alert(result.error.message);
                                    }
                                });
                            } else {
                                console.log( response );
                                alert( response.payload.message );
                            }

                            $('.learndash_checkout_buttons').removeClass('ld-loading');
                            $('style.ld-stripe-css').remove();
                            $('.learndash_checkout_buttons .learndash_checkout_button').css({
                                backgroundColor: ''
                            });
                        });
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Output transaction message
     * @return void
     */
    public function output_transaction_message() {
        if ( ! isset( $_GET['ld_stripe'] ) || empty( $_GET['ld_stripe'] ) ) {
            return;
        }

        switch ( $_GET['ld_stripe'] ) {
            case 'success':
                $message = __( 'Your transaction was successful. Please log in to access your course.', 'learndash-stripe' );
                break;

            default:
                $message = false;
                break;
        }

        if ( ! $message ) {
            return;
        }

        ?>

        <script type="text/javascript">
            jQuery( document ).ready( function( $ ) {
                alert( '<?php echo $message ?>' );
            });
        </script>

        <?php
    }

    /**
     * Record transaction in database
     * @param  Stripe\Checkout\Session $session  Transaction data passed through $_POST
     * @param  int    $course_id    Post ID of a course
     * @param  int    $user_id      ID of a user
     * @param  string $user_email   Email of the user
     */
    public function record_transaction( $session, $course_id, $user_id, $user_email, $payment_number = null ) {
        $course_args = $this->get_course_args( $course_id );

	    $transaction_id   = null;
	    $transaction_data = array();

        if ( strpos( $session->object, 'checkout' ) !== false && ! $session->subscription ) {
            $currency = $session->currency;
            $amount   = $session->amount_total;

            $transaction_data = array(
	            'stripe_nonce'          => 'n/a',
	            'stripe_session_id'     => $session->id,
	            'stripe_metadata'       => $session->metadata,
	            'stripe_customer'       => $session->customer,
	            'stripe_payment_intent' => $session->payment_intent,
	            'customer_email'        => $user_email,
	            'stripe_price'          => ! $this->is_zero_decimal_currency( $currency ) && $amount > 0
		            ? number_format( $amount / 100, 2, '.', '' )
		            : $amount,
	            'stripe_currency'       => $currency,
	            'stripe_name'           => get_the_title( $course_id ),
	            'user_id'               => $user_id,
	            'course_id'             => $course_id,
	            'post_id'               => $course_id,
	            'subscription'          => $session->subscription,
	            'stripe_price_type'     => $course_args['course_price_type'],
            );

            $transaction_id = wp_insert_post( array( 'post_title' => sprintf( _x( "%s %s Purchased By %s", 'Course/Group Name Purchased By mail@domain.com', 'learndash-stripe' ), $course_args['course_type'], $transaction_data['stripe_name'], $user_email ), 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );
        } elseif ( $session->object == 'invoice' && ! empty( $session->subscription ) ) {
            $currency = $session->currency;
            $amount   = $session->total;

            $transaction_data = array(
                'stripe_nonce'          => 'n/a',
                'stripe_session_id'     => $session->id,
                'stripe_metadata'       => $session->metadata,
                'stripe_customer'       => $session->customer,
                'stripe_payment_intent' => $session->payment_intent,
                'customer_email'        => $user_email,
                'stripe_price'          => ! $this->is_zero_decimal_currency( $currency ) && $amount > 0
	                ? number_format( $amount / 100, 2, '.', '' )
	                : $amount,
                'stripe_currency'       => $currency,
                'stripe_name'           => get_the_title( $course_id ),
                'user_id'               => $user_id,
                'course_id'             => $course_id,
                'post_id'               => $course_id,
                'subscription'          => $session->subscription,
                'stripe_price_type'     => $course_args['course_price_type'],
            );

            if ( $payment_number == 1 ) {
                $post_title = sprintf( _x( "Subscription for %s %s Started By %s", 'Subscription for Course/Group Name Started By mail@domain.com', 'learndash-stripe' ), $course_args['course_type'], $transaction_data['stripe_name'], $user_email );
            } else {
                $post_title = sprintf( _x( "Subscription Recurring Payment Received for %s %s By %s", 'Subscription Recurring Payment Received for Course/Group Name By mail@domain.com', 'learndash-stripe' ), $course_args['course_type'], $transaction_data['stripe_name'], $user_email );
            }

            $transaction_id = wp_insert_post( array( 'post_title' => $post_title, 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );
        }

	    if ( $transaction_id > 0 && ! ( $transaction_id instanceof WP_Error ) ) {
            foreach ( $transaction_data as $key => $value ) {
                update_post_meta( $transaction_id, $key, $value );
            }

            do_action( 'learndash_transaction_created', $transaction_id );
        }
    }

    /**
     * Add Customer to Stripe
     * @param int    $user_id     ID of a user
     * @param int    $customer_id Stripe customer ID
     * @param string $email       Email of a user, got from token
     * @return string Stripe customer ID
     */
    public function add_stripe_customer( $user_id, $customer_id, $email ) {
        $this->config();

        try {
            $customer = \Stripe\Customer::retrieve( $customer_id );

            if ( isset( $customer->deleted ) && $customer->deleted ) {
                $customer = \Stripe\Customer::create( array(
                    'email'  => $email,
                ) );
            }

            $customer_id = $customer->id;

            update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );
        } catch ( Exception $e ) {
            $customer = \Stripe\Customer::create( array(
                'email'  => $email,
            ) );

            $customer_id = $customer->id;

            update_user_meta( $user_id, $this->stripe_customer_id_meta_key, $customer_id );
        }

        return $customer_id;
    }
}

global $ld_stripe_checkout;
$ld_stripe_checkout = new LearnDash_Stripe_Checkout_Integration();
