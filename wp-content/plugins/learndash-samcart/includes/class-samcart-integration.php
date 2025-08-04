<?php

/**
* Samcart integration class
*/
class LearnDash_Samcart_Integration {

	public function __construct()
	{
		add_action( 'init', array( $this, 'process_notification_url' ) );
	}

	/**
	 * Process Samcart notification URL HTTP POST
	 */
	public function process_notification_url() {
		if ( ! isset( $_GET['learndash-integration'] ) || $_GET['learndash-integration'] != 'samcart' ) {
			return;
		}

		$post = json_decode( file_get_contents( 'php://input' ), true );
		$post = $this->sanitize_array( $post );

		/**
		 * Filter to decide wheter continue the process or not
		 *
		 * @var bool 		true to continue|false otherwise
		 * @var array $post HTTP POST data sent by Samcart
		 */
		if ( ! apply_filters( 'learndash_samcart_process_notification_url', true, $post ) ) {
			return;
		}

		if ( ! empty( $post['type'] ) ) {
			$type     = $post['type'];
			$product  = $post['product'];
			$customer = $post['customer'];
			$order    = $post['order'];

			// retrieve or create user if doesn't exist
			$user = get_user_by( 'email', $customer['email'] );
			if ( false !== $user ) {
				$user_id = $user->ID;
			} else {
				$username = preg_replace( '/(.*)\@(.*)/', '$1', $customer['email'] );
				$password = wp_generate_password( 12, true, false );
				$user_id  = $this->create_user( $username, $password, $customer );
				$user     = get_user_by( 'id', $user_id );
				wp_new_user_notification( $user_id, null, 'both' );
			}

			$args = array(
				'post_type'  => 'ld-samcart-product',
				'meta_query' => array(
					array(
						'key'   => '_ld_samcart_product_id',
						'value' => $product['id'],
					)
				)
			);

			$ld_products = new WP_Query( $args );

			$remove = in_array( $type, array( 'Refund', 'Cancel', 'RecurringPaymentFailed' ) ) ? true : false;

			if ( $ld_products->have_posts() ) {
				while ( $ld_products->have_posts() ) {
					$ld_products->the_post();

					$courses = (array) get_post_meta( get_the_ID(), '_ld_samcart_courses', true );

					foreach ( $courses as $course_id ) {
						ld_update_course_access( $user_id, $course_id, $remove );
					}
				}
			} else {
				error_log( sprintf( __( 'No LearnDash Samcart product with product ID: %d. Please create it first.', 'learndash-samcart' ), $product['id'] ) );
			}

			wp_reset_query();

			if ( $remove === false ) {
				$this->record_transaction( $post, $product['id'], $user_id, $user->user_email );
			}
		}

		die();
	}

	/**
	 * Sanitize associative array
	 * @param  array  $array Associative array or one dimensional array
	 * @return array         Sanitized array
	 */
	public function sanitize_array( $array )
	{
		foreach ( $array as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$array[ $key ] = sanitize_text_field( $value );
			} else {
				$this->sanitize_array( $value );
			}
		}

		return $array;
	}

	/**
	 * Record transaction in database
	 * @param  array  $post  Transaction data passed through $post
	 * @param  int    $product_id   Post ID of a course
	 * @param  int    $user_id      ID of a user
	 * @param  string $user_email   Email of the user
	 */
	public function record_transaction( $post, $product_id, $user_id, $user_email )
	{
		$post['user_id']    = $user_id;
		$post['product_id'] = $product_id;

		$product_title = $post['product']['name'];

		$post_id = wp_insert_post( 
			array( 
				'post_title'  => sprintf( __( 'Product %s Purchased By %s via Samcart', 'learndash-samcart' ), $product_title, $user_email ), 
				'post_type'   => 'sfwd-transactions', 
				'post_status' => 'publish', 
				'post_author' => $user_id 
		) );

		$this->record_transaction_meta( $post_id, $post );
	}

	/**
	 * Record transaction meta
	 * @param  int    $post_id Transaction post ID
	 * @param  array  $array   $_POST data
	 * @param  string $parent  Parent key
	 */
	public function record_transaction_meta( $post_id, $array, $parent = null )
	{
		foreach ( $array as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$prefix = isset( $parent ) ? $parent . '_' : '';
				update_post_meta( $post_id, $prefix . $key, $value );
			} else {
				$this->record_transaction_meta( $post_id, $value, $key );
			}	
		}
	}

	/**
	 * Create user if not exists
	 * 
	 * @param  string $username 
	 * @param  string $password 
	 * @param  array  $customer  Samcart customer data  
	 * @return int               Newly created user ID
	 */
	public function create_user( $username, $password, $customer ) {
		$user_id  = wp_create_user( $username, $password, $customer['email'] );

		if ( is_wp_error( $user_id ) ) {
			if ( $user_id->get_error_code() == 'existing_user_login' ) {
				$random_chars = str_shuffle( substr( md5( time() ), 0, 5 ) );
				$username = $username . '-' . $random_chars;
				$user_id = $this->create_user( $username, $password, $customer );
			}
		}

		$user = get_user_by( 'ID', $user_id );
		$user->first_name = $customer['first_name'];
		$user->last_name  = $customer['last_name'];

		$update_user = wp_insert_user( $user );

		do_action( 'learndash_samcart_after_create_user', $user_id, $customer );

		return $user_id;
	}
}

new LearnDash_Samcart_Integration();