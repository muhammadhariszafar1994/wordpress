<?php

add_action( 'init', 'learndash_2checkout_checkout_process', 1 );

function learndash_2checkout_checkout_process()
{
	if ( ! isset( $_GET['learndash-checkout'] ) || $_GET['learndash-checkout'] != '2co' ) {
		return;
	}

	/**
	 *  2Checkout-IPN Handler
	 */

	if ( empty( $_REQUEST ) || empty( $_REQUEST['email'] ) )
	{
		error_log( 'Empty required details: ' . print_r( $_REQUEST, true ) );
		exit( 0 );
	}

	$user_id   = $_REQUEST['user_id'];
	$course_id = $_REQUEST['course_id'];
	$post_type = get_post_type( $course_id );
	$email     = $_REQUEST['email'];
	$credit_card_processed = $_REQUEST['credit_card_processed'];

	$settings     = get_option( 'learndash_2checkout_settings' );

	if ( $post_type == 'sfwd-courses' ) {
		$total        = number_format( floatval( learndash_get_setting( $course_id, 'course_price' ) ), 2, '.', ',' );
	} elseif ( $post_type == 'groups' ) {
		$total        = number_format( floatval( learndash_get_setting( $course_id, 'group_price' ) ), 2, '.', ',' );
	}

	$sid          = $settings['sid'];
	$secret_word  = $settings['secret_word'];
	$order_number = $settings['demo'] != 1 ? $_REQUEST['order_number'] : 1;
	$hash_key     = strtoupper( md5( $secret_word . $sid . $order_number . $total ) );

	if ( empty( $credit_card_processed ) || $credit_card_processed != 'Y' ) {
		error_log( 'Card Not Processed. ' . print_r( $_REQUEST, true ) );
		exit( 0 );
	} else if ( $hash_key != $_REQUEST['key'] ) {
		error_log( 'Hash key doen\'t match with 2CO hash key. The transaction is not validated. ' . print_r( $_REQUEST, true ) );
		exit( 0 );
	} else {
		$verified = 1;
	}

	$YOUR_NOTIFICATION_EMAIL_ADDRESS = get_option( 'admin_email' );

	if ( $verified ) {
		//a customer has purchased from this website
		//add him to database for customer support
		// get / add user
					
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
		} else {
			if ( ! empty( $user_id ) )
			{
				$user = get_user_by( 'id', $user_id );
			}
			else if ( $user_id = email_exists( $email ) ) {
				$user = get_user_by( 'id', $user_id );
			} else {
				$username = $email;
				if ( username_exists( $email ) ) {
					$count = 1;
					do {
						$new_username = $count . '_' . $email;
						$count++;
					} while ( username_exists( $new_username ) );
					$username = $new_username;
				}
				$random_password = wp_generate_password( 12, false );
				$user_id = wp_create_user( $username, $random_password, $email );
				$user = get_user_by( 'id', $user_id );
				wp_new_user_notification( $user_id, $random_password, 'both' );
			}
		}

		if ( $post_type == 'sfwd-courses' ) {
			ld_update_course_access( $user_id, $course_id );
		} elseif ( $post_type == 'groups' ) {
			ld_update_group_access( $user_id, $course_id );
		}
		
		// log transaction
		$transaction = $_REQUEST;
		$transaction['user_id'] = $user_id;
		$transaction['course_id'] = $course_id;
		
		$course_title = '';
		$course = get_post( $course_id );
		if ( ! empty( $course ) )
			$course_title = $course->post_title;
		
		$post_id = wp_insert_post( array( 'post_title' => "Course {$course_title} Purchased By {$email}", 'post_type' => 'sfwd-transactions', 'post_status' => 'publish', 'post_author' => $user_id ) );
		foreach ( $transaction as $k => $v ) {
			update_post_meta( $post_id, $k, $v );
		}

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			print_r( 'LearnDash 2Checkout verified transaction: ' . $_REQUEST, true );
		}
		
		$permalink = add_query_arg( [ 'learndash-2co-message' => 'success' ], get_permalink( $course_id ) );
		header( 'Location: ' . $permalink );
		exit( 0 );

	} else {
		/*An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's a good idea to have a developer or sys admin 
		manually investigate any invalid IPN.*/

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			print_r( 'LearnDash 2Checkout invalid transaction: ' . $_REQUEST, true );
		}

		$permalink = add_query_arg( [ 'learndash-2co-message' => 'invalid' ], get_permalink( $course_id ) );
		header( 'Location: ' . get_bloginfo( 'wpurl' ) );
		exit( 0 );
		
	}

	//we're done here	
}