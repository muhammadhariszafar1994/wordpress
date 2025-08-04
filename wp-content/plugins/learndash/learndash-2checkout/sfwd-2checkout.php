<?php
/*
Plugin Name: LearnDash LMS - 2Checkout Integration
Plugin URI: http://www.learndash.com
Description: 2Checkout payment gateway for LearnDash LMS.
Version: 1.1.1.2
Author: LearnDash
Author URI: http://www.learndash.com
Text Domain: learndash-2checkout
Doman Path: /languages/
*/

// Plugin version
if ( ! defined( 'LEARNDASH_2CHECKOUT_VERSION' ) ) {
	define( 'LEARNDASH_2CHECKOUT_VERSION', '1.1.1.2' );
}

// Plugin file
if ( ! defined( 'LEARNDASH_2CHECKOUT_FILE' ) ) {
	define( 'LEARNDASH_2CHECKOUT_FILE', __FILE__ );
}

// Plugin folder path
if ( ! defined( 'LEARNDASH_2CHECKOUT_PLUGIN_PATH' ) ) {
	define( 'LEARNDASH_2CHECKOUT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL
if ( ! defined( 'LEARNDASH_2CHECKOUT_PLUGIN_URL' ) ) {
	define( 'LEARNDASH_2CHECKOUT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Check plugin dependencies
 * @return void
 */
function learndash_2checkout_check_dependency()
{
	include LEARNDASH_2CHECKOUT_PLUGIN_PATH . 'includes/class-dependency-check.php';

	LearnDash_Dependency_Check_LD_2Checkout::get_instance()->set_dependencies(
		array(
			'sfwd-lms/sfwd_lms.php' => array(
				'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
				'class'       => 'SFWD_LMS',
				'min_version' => '3.0.0',
			),
		)
	);

	LearnDash_Dependency_Check_LD_2Checkout::get_instance()->set_message(
		__( 'LearnDash LMS - 2Checkout Add-on requires the following plugin(s) to be active:', 'learndash-2checkout' )
	);
}

learndash_2checkout_check_dependency();
add_action( 'plugins_loaded', 'learndash_2checkout_init', 20 );

function learndash_2checkout_init() {
	if ( LearnDash_Dependency_Check_LD_2Checkout::get_instance()->check_dependency_results() ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		include_once( plugin_dir_path( __FILE__ ) . 'checkout_process.php' );

		if ( is_admin() ) {
			include_once LEARNDASH_2CHECKOUT_PLUGIN_PATH . 'includes/admin/class-settings-section.php';
		}

		add_filter( 'learndash_payment_button', 'learndash_payment_button_2checkout', 1, 2 );

		add_action( 'wp_footer', 'learndash_2checkout_output_message' );
	}
}

add_action( 'plugins_loaded', 'learndash_2checkout_i18ize' );

function learndash_2checkout_i18ize() {
	load_plugin_textdomain( 'learndash_2checkout', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// include translation/update class
	include LEARNDASH_2CHECKOUT_PLUGIN_PATH . 'includes/class-translations-ld-2checkout.php';
}

function learndash_payment_button_2checkout( $button, $params = null ) {
	$learndash_2checkout_settings = get_option( 'learndash_2checkout_settings' );

	if ( isset( $learndash_2checkout_settings['enabled'] ) && empty( $learndash_2checkout_settings['enabled'] ) ) {
		return $button;
	}

	$sid = ! empty( $learndash_2checkout_settings['sid'] ) ? $learndash_2checkout_settings['sid'] : '';
	$demo = ! empty( $learndash_2checkout_settings['demo'] ) ? 'Y' : 'N';

	if ( empty( $learndash_2checkout_settings ) || empty( $sid ) || empty( $params ) || empty( $params['price'] ) || empty( $params['post'] ) || empty( $params['post']->ID ) )
		return $button;

	$price = number_format( $params['price'], 2, '.', '' );
	$post =  $params['post'];
	$title = $post->post_title;
	$productid = $post->ID;
	$order_id = time() . '-' . rand( 100, 999 );

	$action_url = 'https://www.2checkout.com/checkout/purchase';
	$x_receipt_link_url = site_url( '/' ) . '?learndash-checkout=2co';

	$current_user = wp_get_current_user();
	$user_id = empty( $current_user->ID ) ? 0 : $current_user->ID;
	$price_type = learndash_get_setting( $productid, 'course_price_type' );
	$recurring_cycle_number = get_post_meta( $productid, 'course_price_billing_p3', true );
	$recurring_cycle = get_post_meta( $productid, 'course_price_billing_t3', true );

	switch ( $recurring_cycle ) {
		case 'D':
			$recurring_cycle_period = 'Day';
			break;

		case 'W':
			$recurring_cycle_period = 'Week';
			break;

		case 'M':
			$recurring_cycle_period = 'Month';
			break;

		case 'Y':
			$recurring_cycle_period = 'Year';
			break;

		default:
			$recurring_cycle_period = 'Day';
			break;
	}

	$recurrence = $price_type == 'subscribe' ? '<input type="hidden" name="li_0_recurrence" value="' . esc_attr( $recurring_cycle_number . ' ' . $recurring_cycle_period ) . '">' : '';

	if ( strstr( $button, 'learndash_checkout_button' ) ) {
		$button_text  = apply_filters( 'learndash_2checkout_purchase_button_text', __( 'Use 2Checkout', 'learndash-2checkout' ) );
	} else {
	    if ( class_exists( 'LearnDash_Custom_Label' ) ) {
	        $button_text  = apply_filters( 'learndash_2checkout_purchase_button_text', LearnDash_Custom_Label::get_label( 'button_take_this_course' ) );
	    } else {
	        $button_text  = apply_filters( 'learndash_2checkout_purchase_button_text', __( 'Take This Course', 'learndash-stripe' ) );
	    }
	}

	$checkout = '<div class="learndash_checkout_button learndash_2checkout_button">
		<form action="' . esc_url( $action_url ) . '" method="post">
			<input type="hidden" name="sid" value="' . esc_attr( $sid ) . '" >
			<input type="hidden" name="mode" value="2CO" >
			<input type="hidden" name="li_0_type" value="product" >
			<input type="hidden" name="li_0_name" value="' . esc_attr( $title ) . '" >
			' . $recurrence . '
			<input type="hidden" name="li_0_product_id" value="' . esc_attr( $productid ) . '" >
			<input type="hidden" name="li_0_price" value="' . esc_attr( $price ) . '" >
			<input type="hidden" name="li_0_quantity" value="1" >
			<input type="hidden" name="li_0_tangible" value="N" >
			<input type="hidden" name="demo" value="' . esc_attr( $demo ) . '" >
			<input type="hidden" name="merchant_order_id" value="Order ID: ' . esc_attr( $order_id ) . '" >
			<input type="hidden" name="user_id" value="' . esc_attr( $user_id ) . '" >
			<input type="hidden" name="course_id" value="' . esc_attr( $productid ) . '" >
			<input type="hidden" name="x_receipt_link_url" value="' . esc_attr( $x_receipt_link_url ) . '" >
			<input type="submit" class="learndash-2checkout-checkout-button btn-join button" value="' . esc_attr( $button_text ) . '"/>
		</form>
	</div>';

	return $button . $checkout;
}

function learndash_2checkout_output_message()
{
	if ( empty( $_GET['learndash-2co-message'] ) ) {
		return;
	}

	$type = sanitize_text_field( $_GET['learndash-2co-message'] );

	$message = '';
	switch ( $type ) {
		case 'success':
			$message = __( 'Your transaction is successful. Please check your email for the login credentials.', 'learndash-2checkout' );
			break;

		case 'invalid':
			$message = __( 'Your transaction is invalid. Please contact our web administrator to resolve this issue.', 'learndash-2checkout' );
			break;
	}

	$message = apply_filters( 'learndash_2checkout_message', $message, $type );

	if ( empty( $message ) ) {
		return;
	}
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			alert( '<?php echo esc_attr( $message ) ?>' );
		} );
	</script>
	<?php
}
