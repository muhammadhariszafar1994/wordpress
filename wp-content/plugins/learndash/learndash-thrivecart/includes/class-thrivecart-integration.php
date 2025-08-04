<?php
/**
 * Integration class file.
 *
 * @since 1.0.0
 *
 * @package LearnDash\Thrivecart
 */

/**
 * Thrivecart integration class.
 *
 * @since 1.0.0
 */
class LearnDash_Thrivecart_Integration {
	/**
	 * Hook name for course access removal in WP Cron.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private static $hook_name_access_removal = 'learndash_thrivecart_access_removal';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'learndash_thrivecart_process_webhook', [ $this, 'filter_webhook_process' ], 5, 2 );

		add_action( 'init', array( $this, 'process_webhook_url' ) );
		add_action( self::$hook_name_access_removal, [ $this, 'remove_access_on_removal_time' ], 10, 3 );
	}

	/**
	 * Filter webhook processing.
	 *
	 * @since 1.0.2 Add logic to check partial refund behavior.
	 *
	 * @param bool  $allow True to continue webhook processing, false to abort. Default is true.
	 * @param array $data  Webhook data.
	 *
	 * @return bool Final $allow value.
	 */
	public function filter_webhook_process( $allow, $data ): bool {
		if ( $data['event'] !== 'order.refund' ) {
			return $allow;
		}

		$behavior = LearnDash_Thrivecart_Settings_Section::get_setting( 'partial_refund_behavior', '' );

		// If the behavior is set to 'keep' and partial refund happens, abort the webhook processing.

		if (
			$behavior === 'keep'
			&& $data['order']['total'] !== $data['refund']['amount']
		) {
			return false;
		}

		return $allow;
	}

	/**
	 * Process Thrivecart notification URL HTTP POST.
	 */
	public function process_webhook_url() {
		if ( ! isset( $_GET['learndash-integration'] ) || $_GET['learndash-integration'] != 'thrivecart' ) {
			return;
		}

		if ( ! empty( $_POST ) && ! $this->is_transaction_valid( $_POST ) ) {
			wp_die( __( 'Cheatin\' huh?', 'learndash-thrivecart' ), '', array( 'response' => 200 ) );
		}

		$_POST = $this->sanitize_array( $_POST );

		if ( ! apply_filters( 'learndash_thrivecart_process_webhook', true, $_POST ) ) {
			return;
		}

		if ( ! empty( $_POST['order'] ) ) {
			$thrivecart = $_POST;
			$type       = $thrivecart['event'];
			$customer   = $thrivecart['customer'];

			if ( $type == 'order.success' ) {
				$purchases = $thrivecart['purchase_map'];

				foreach ( $purchases as $product_id ) {
					$access = $this->add_access( $customer, $product_id );
				}
			} elseif ( $type == 'order.refund' ) {
				$refund     = $thrivecart['refund'];
				$product_id = $refund['type'] . '-' . $refund['id'];

				$access = $this->remove_access( $customer, $product_id );
			} elseif ( $type == 'order.subscription_payment' || $type == 'order.subscription_completed' ) {
				$subscription = $thrivecart['subscription'];
				$product_id   = $subscription['type'] . '-' . $subscription['id'];

				$access = $this->add_access( $customer, $product_id );
			} elseif ( $type == 'order.subscription_cancelled' ) {
				$subscription = $thrivecart['subscription'];
				$product_id   = $subscription['type'] . '-' . $subscription['id'];
				$removal_time = ! empty( $subscription['billing_period_end'] ) ? strtotime( $subscription['billing_period_end'] ) : null;

				$access = $this->remove_access( $customer, $product_id, $removal_time );
			}

			if ( ! empty( $access['access_ids'] ) ) {
				$this->record_transaction( $_POST, $access['product_title'], $access['access_ids'], $access['user'], $access['remove'] );
			}

			wp_die( __( 'Success.', 'learndash-thrivecart' ), '', array( 'response' => 200 ) );
		} else {
			wp_die( __( 'There\'s no purchase in your transaction.', 'learndash-thrivecart' ), '', array( 'response' => 200 ) );
		}

	}

	/**
	 * Add course access to the user based on Thrivecart product ID
	 *
	 * @param array $customer   Customer details array
	 * @param int   $product_id Thrivecart product ID
	 */
	public function add_access( $customer, $product_id ) {
		if ( false !== strpos( $product_id, 'product' ) ) {
			preg_match( '/product-(\d+)/', $product_id, $matches );
			$alt_product_id = $matches[1];
		}

		$args = array(
			'post_type'  => 'ld-thrivecart',
			'meta_query' => array(
				array(
					'key'   => '_ld_thrivecart_product_id',
					'value' => $product_id,
				),
			),
		);

		if ( false !== strpos( $product_id, 'product' ) ) {
			$args['meta_query'][] = array(
				'key'   => '_ld_thrivecart_product_id',
				'value' => $alt_product_id,
			);

			$args['meta_query']['relation'] = 'OR';
		}

		$ld_products = new WP_Query( $args );

		$product_title = '';
		$access_ids    = array();
		$remove        = false;
		$user          = '';

		if ( $ld_products->have_posts() ) {
			$user    = $this->maybe_create_user( $customer );
			$user_id = $user->ID;

			while ( $ld_products->have_posts() ) {
				$ld_products->the_post();

				$product_title = get_the_title();

				$courses = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_courses', true );
				$groups  = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_groups', true );

				foreach ( $courses as $course_id ) {
					$access_ids[] = $course_id;
					ld_update_course_access( $user_id, $course_id );
				}

				foreach ( $groups as $group_id ) {
					$access_ids[] = $group_id;
					ld_update_group_access( $user_id, $group_id );
				}
			}

			wp_reset_postdata();
		}

		wp_reset_query();

		return compact( 'product_title', 'access_ids', 'remove', 'user' );
	}

	/**
	 * Remove course access from the user based on Thrivecart product ID.
	 *
	 * @since 1.0 Initial version.
	 * @since 1.0.2 Add $removal_time parameter to schedule the access removal.
	 *
	 * @param array $customer     Customer details array.
	 * @param int   $product_id   Thrivecart product ID.
	 * @param ?int  $removal_time Timestamp when to remove user course access.
	 *
	 * @return array{product_title: string, access_ids: array<int>, remove: bool, user: ?WP_User} Access removal details.
	 */
	private function remove_access( $customer, $product_id, ?int $removal_time = null ) {
		if ( false !== strpos( $product_id, 'product' ) ) {
			preg_match( '/product-(\d+)/', $product_id, $matches );
			$alt_product_id = $matches[1];
		}

		$args = array(
			'post_type'  => 'ld-thrivecart',
			'meta_query' => array(
				array(
					'key'   => '_ld_thrivecart_product_id',
					'value' => $product_id,
				),
			),
		);

		if ( false !== strpos( $product_id, 'product' ) ) {
			$args['meta_query'][] = array(
				'key'   => '_ld_thrivecart_product_id',
				'value' => $alt_product_id,
			);

			$args['meta_query']['relation'] = 'OR';
		}

		$ld_products = new WP_Query( $args );

		$product_title = '';
		$access_ids    = array();
		$remove        = true;
		$user          = null;

		if ( $ld_products->have_posts() ) {
			$user    = $this->maybe_create_user( $customer );
			$user_id = $user->ID;

			while ( $ld_products->have_posts() ) {
				$ld_products->the_post();

				$product_title = get_the_title();

				$courses = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_courses', true );
				$groups  = (array) get_post_meta( get_the_ID(), '_ld_thrivecart_groups', true );

				if ( empty( $removal_time ) ) {
					foreach ( $courses as $course_id ) {
						$access_ids[] = $course_id;
						ld_update_course_access( $user_id, $course_id, $remove );
					}

					foreach ( $groups as $group_id ) {
						$access_ids[] = $group_id;
						ld_update_group_access( $user_id, $group_id, $remove );
					}
				} else {
					$this->schedule_access_removal(
						$removal_time,
						[
							$user_id,
							$courses,
							$groups,
						]
					);
				}
			}

			wp_reset_postdata();
		}

		wp_reset_query();

		return compact( 'product_title', 'access_ids', 'remove', 'user' );
	}

	/**
	 * Schedule access removal using WP cron.
	 *
	 * @since 1.0.2
	 *
	 * @param int                  $removal_time Removal timestamp.
	 * @param array<string, mixed> $args         WP Cron args.
	 *
	 * @return void
	 */
	private function schedule_access_removal( int $removal_time, array $args ): void {
		if ( ! wp_next_scheduled( self::$hook_name_access_removal, $args ) ) {
			wp_schedule_single_event( $removal_time, self::$hook_name_access_removal, $args );
		}
	}

	/**
	 * Remove access on set removal time in WP cron.
	 *
	 * @since 1.0.2
	 *
	 * @param int        $user_id    User ID.
	 * @param array<int> $course_ids List of course IDs.
	 * @param array<int> $group_ids  List of group IDs.
	 *
	 * @return void
	 */
	public function remove_access_on_removal_time( int $user_id = 0, array $course_ids = [], array $group_ids = [] ): void {
		foreach ( $course_ids as $course_id ) {
			ld_update_course_access( $user_id, $course_id, true );
		}

		foreach ( $group_ids as $group_id ) {
			ld_update_group_access( $user_id, $group_id, true );
		}
	}

	/**
	 * Create or retrieve existing user.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $customer Customer data passed by Thrivecart webhook.
	 *
	 * @return object WP_User object.
	 */
	public function maybe_create_user( $customer ) {
		// retrieve or create user if doesn't exist
		$user = get_user_by( 'email', $customer['email'] );

		if ( false === $user ) {
			$password = wp_generate_password( 12, true, false );
			$username = preg_replace( '/(.*)\@.*/', '$1', $customer['email'] );
			$user_id  = $this->create_user( $username, $password, $customer );
			$user     = get_user_by( 'id', $user_id );
			wp_new_user_notification( $user_id, null, 'both' );
		}

		return $user;
	}

	/**
	 * Create user if not exists.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $username Username.
	 * @param  string $password Password.
	 * @param  array  $customer Thrivecart customer data.
	 *
	 * @return int              Newly created user ID.
	 */
	public function create_user( $username, $password, $customer ) {
		$user_id = wp_create_user( $username, $password, $customer['email'] );

		if ( is_wp_error( $user_id ) ) {
			if ( $user_id->get_error_code() == 'existing_user_login' ) {
				$random_chars = str_shuffle( substr( md5( time() ), 0, 5 ) );
				$username     = $username . '-' . $random_chars;
				$user_id      = $this->create_user( $username, $password, $customer );
			}
		}

		$user             = get_user_by( 'ID', $user_id );
		$user->first_name = $customer['first_name'];
		$user->last_name  = $customer['last_name'];

		$update_user = wp_insert_user( $user );

		do_action( 'learndash_thrivecart_after_create_user', $user_id, $customer, $password );

		return $user_id;
	}

	/**
	 * Sanitize associative array.
	 *
	 * @param  array $array Associative array or one dimensional array.
	 *
	 * @return array         Sanitized array
	 */
	public function sanitize_array( $array ) {
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
	 *
	 * @param  array   $_post         Thrivecart POST data
	 * @param  string  $product_title Product title
	 * @param  array   $course_ids    Associated course ids
	 * @param  object  $user          WP_User object
	 * @param  boolean $refund        True if refund|false otherwise
	 * @return void
	 */
	public function record_transaction( $_post, $product_title, $course_ids, $user, $refund = false ) {
		 $_post['user_id']  = $user->ID;
		$_post['course_id'] = implode( ', ', $course_ids );

		// translators: 1$: Product title, 2$: user email.
		$post_title = $refund ? sprintf( __( 'Product %1$s Refunded By %2$s via Thrivecart', 'learndash-thrivecart' ), $product_title, $user->user_email ) : sprintf( __( 'Product %1$s Purchased By %2$s via Thrivecart', 'learndash-thrivecart' ), $product_title, $user->user_email );

		$post_id = wp_insert_post(
			array(
				'post_title'  => $post_title,
				'post_type'   => 'sfwd-transactions',
				'post_status' => 'publish',
				'post_author' => $user->ID,
			)
		);

		$this->record_transaction_meta( $post_id, $_post );
	}

	/**
	 * Record transaction meta
	 *
	 * @param  int    $post_id Transaction post ID
	 * @param  array  $array   $_POST data
	 * @param  string $parent  Parent key
	 */
	public function record_transaction_meta( $post_id, $array, $parent = array() ) {
		foreach ( $array as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$prefix = ! empty( $parent ) ? implode( '_', $parent ) : '';
				if ( ! empty( $prefix ) ) {
					$prefix = $prefix . '_';
				}

				update_post_meta( $post_id, $prefix . $key, $value );
			} else {
				$new_parent = array( $key );
				$parent_key = array_merge( $parent, $new_parent );

				$this->record_transaction_meta( $post_id, $value, $parent_key );
			}
		}
	}

	/**
	 * Check if thrivecart transaction is valid.
	 *
	 * @param  array $_post Transaction query string.
	 *
	 * @return boolean
	 */
	public function is_transaction_valid( $_post ) {
		if ( isset( $_post['thrivecart_secret'] ) ) {
			$secret  = $_post['thrivecart_secret'];
			$options = get_option( 'learndash_thrivecart_settings', array() );

			if ( ! isset( $options['secret_word'] ) ) {
				wp_die( __( 'Thrivecart secret word is not set up on this site. Please contact website administrator.', 'learndash-thrivecart' ), '', array( 'response' => 200 ) );
			}

			$check = trim( $options['secret_word'] );

			if ( $secret === $check ) {
				return true;
			}
		}

		return false;
	}
}

new LearnDash_Thrivecart_Integration();
