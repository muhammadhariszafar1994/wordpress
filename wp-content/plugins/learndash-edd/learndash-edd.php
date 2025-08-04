<?php
/**
 * Plugin Name:     LearnDash LMS - EDD Integration
 * Plugin URI:      http://learndash.com
 * Description:     LearnDash integration plugin for Easy Digital Downloads
 * Version:         1.4.0
 * Author:          LearnDash
 * Author URI:      http://learndash.com
 * Text Domain:     learndash-edd
 * Domain Path: 	/languages/
 *
 * @package         LearnDash\EDD
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'LearnDash_EDD' ) ) {


    /**
     * Main LearnDash_EDD class
     *
     * @since       1.0.0
     */
    class LearnDash_EDD {


        /**
         * @var         LearnDash_EDD $instance The one true LearnDash_EDD
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true LearnDash_EDD
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new LearnDash_EDD();
                self::$instance->setup_constants();

                add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

                self::$instance->check_dependency();
                self::$instance->includes();

                add_action( 'plugins_loaded', function() {
                    if ( LearnDash_Dependency_Check_LD_EDD::get_instance()->check_dependency_results() ) {
                        self::$instance->includes_on_plugins_loaded();
                        self::$instance->hooks();
                    }
                } );
            }

            return self::$instance;
        }

        public function check_dependency()
        {
            include LEARNDASH_EDD_DIR . 'includes/class-dependency-check.php';

            LearnDash_Dependency_Check_LD_EDD::get_instance()->set_dependencies(
                array(
                    'sfwd-lms/sfwd_lms.php' => array(
                        'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
                        'class'       => 'SFWD_LMS',
                        'min_version' => '3.0.0',
                    ),
                    'easy-digital-downloads/easy-digital-downloads.php' => array(
                        'label'       => '<a href="https://easydigitaldownloads.com/">Easy Digital Downloads</a>',
                        'class'       => 'Easy_Digital_Downloads',
                        'min_version' => '2.9.0',
                    ),
					'easy-digital-downloads-pro/easy-digital-downloads.php' => array(
                        'label'       => '<a href="https://easydigitaldownloads.com/">Easy Digital Downloads Pro</a>',
                        'class'       => 'Easy_Digital_Downloads',
                        'min_version' => '3.1.1.2',
                    ),
                )
            );

            LearnDash_Dependency_Check_LD_EDD::get_instance()->set_message(
                __( 'LearnDash LMS - EDD Add-on requires the following plugin(s) to be active:', 'learndash-edd' )
            );
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'LEARNDASH_EDD_VERSION', '1.4.0' );

            // File
            define( 'LEARNDASH_EDD_FILE', __FILE__ );

            // Plugin path
            define( 'LEARNDASH_EDD_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'LEARNDASH_EDD_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Include required files
         *
         * @return void
         */
        private function includes()
        {
            require_once LEARNDASH_EDD_DIR . 'includes/class-cron.php';
        }

        /**
         * Include required files on plugins_loaded hook
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes_on_plugins_loaded() {
            require_once LEARNDASH_EDD_DIR . 'includes/functions.php';
            require_once LEARNDASH_EDD_DIR . 'includes/scripts.php';

            if ( is_admin() ) {
                require_once LEARNDASH_EDD_DIR . 'includes/metabox.php';
                require_once LEARNDASH_EDD_DIR . 'includes/admin/class-settings-section.php';
            }

			// Prior to this release we have some debug output being written to a log file in the site root.
			// This code is added to remove the file.
			if ( file_exists( ABSPATH.'ld-edd.log' ) ) {
				@unlink( ABSPATH.'ld-edd.log' );
			}
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            add_action( 'admin_enqueue_scripts', [ $this, 'deregister_admin_scripts' ], 10 );
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 10 );

            // Update courses
            add_action( 'edd_updated_edited_purchase', array( $this, 'updated_edited_purchase' ) );
            add_action( 'edd_complete_purchase', array( $this, 'complete_purchase' ) );
            add_action( 'edd_payment_saved', array( $this, 'update_access_by_payment_id' ), 100, 1 );
            add_action( 'edd_free_downloads_post_complete_payment', array( $this, 'update_access_by_payment_id' ), 100, 1 );
            add_action( 'edd_subscription_status_change', array( $this, 'edd_subscription_status_change' ), 10, 3 );

            // Remove courses
            add_action( 'edd_subscription_cancelled', array( $this, 'cancel_subscription' ), 10, 2 );
            add_action( 'edd_subscription_failing', array( $this, 'cancel_subscription_on_failing_subscription' ), 10, 2 );
            add_action( 'edd_subscription_expired', array( $this, 'subscription_expired' ), 10, 2 );
            add_action( 'edd_subscription_completed', array( $this, 'subscription_completed' ), 10, 2 );
            add_action( 'edd_update_payment_status', array( $this, 'remove_access_on_payment_update' ), 10, 3 );
            add_action( 'edd_payment_delete', array( $this, 'remove_access_on_payment_delete' ), 10, 1 );
            add_action( 'init', array( $this, 'check_user_expired_subscription' ), 10 );

            // AJAX
            add_action( 'wp_ajax_ld_edd_retroactive_access', [ $this, 'ajax_retroactive_access' ] );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'learndash_edd_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'learndash-edd', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/learndash-edd' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/learndash-edd/ folder
                load_textdomain( 'learndash-edd', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/learndash-edd/languages/ folder
                load_textdomain( 'learndash-edd', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'learndash-edd', false, $lang_dir );
            }

            // include translation/update class
            include LEARNDASH_EDD_DIR . 'includes/class-translations-ld-edd.php';
        }

        public static function deregister_admin_scripts()
        {
            $screen = get_current_screen();

            if ( $screen->id == 'download' && $screen->base == 'post' ) {
                wp_deregister_script( 'learndash-select2-jquery-script' );
                wp_deregister_style( 'learndash-select2-jquery-style' );
            }
        }

        public function enqueue_admin_scripts()
        {
            $screen = get_current_screen();

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            if ( $screen->id == 'download' && $screen->base == 'post' ) {
                wp_enqueue_style( 'learndash-edd-select2', LEARNDASH_EDD_URL . 'lib/select2/select2.min.css', [], '4.0.13', 'screen' );
                wp_enqueue_script( 'learndash-edd-select2', LEARNDASH_EDD_URL . 'lib/select2/select2.min.js', [ 'jquery' ], '4.0.13', false );
            }

            if ( $screen->id == 'admin_page_learndash_lms_advanced' && isset( $_GET['section-advanced'] ) && $_GET['section-advanced'] == 'settings_edd' ) {
                wp_enqueue_style( 'learndash-edd-tools', LEARNDASH_EDD_URL . 'assets/css/tools' . $suffix . '.css', [], LEARNDASH_EDD_VERSION, 'all' );

                wp_enqueue_script( 'learndash-edd-tools', LEARNDASH_EDD_URL . 'assets/js/tools' . $suffix . '.js', [ 'jquery' ], LEARNDASH_EDD_VERSION, true );

                wp_localize_script( 'learndash-edd-tools', 'LearnDash_EDD_Tools', [
                    'nonce' => [
                        'retroactive_access' => wp_create_nonce( 'ld_edd_retroactive_access' ),
                    ],
                    'text' => [
                        'keep_page_open' => __( 'The retroactive access tool is in progress, please keep the page open.', 'learndash-edd' ),
                        'status' => __( 'Status', 'learndash-edd' ),
                        'retroactive_button' => _x( 'Run', 'Run the retroactive process', 'learndash-edd' ),
                        'complete' => _x( 'Completed', 'Status: completed', 'learndash-edd' ),
                    ]
                ] );
            }
        }

        /**
         * Update LearnDash course access based on transaction ID
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function update_access( $payment_id, $remove = false ) {
            // Get transaction data
			$payment = new EDD_Payment( $payment_id );

			if ( $payment ) {
				$downloads = $payment->downloads;

				if ( ! empty( $downloads ) && is_array( $downloads ) ) {
					foreach( $downloads as $download ) {
						if ( isset( $download['id'] ) ) {
							$download_id = intval( $download['id'] );

							if ( ! empty( $download_id ) ) {
								// Get the customer ID
								$user_id = $payment->user_id;

								if ( ! empty( $user_id ) ) {
                                    $download_obj = new EDD_Download( $download_id );

                                    // Get the courses and groups
                                    if ( $download_obj->has_variable_prices() ) {
                                        $courses = learndash_edd_get_variable_product_courses( $download_id, $download['options']['price_id'] );

                                        $groups = learndash_edd_get_variable_product_groups( $download_id, $download['options']['price_id'] );
                                    } else {
                                        $courses = get_post_meta( $download_id, '_edd_learndash_course', true );
                                        $groups  = get_post_meta( $download_id, '_edd_learndash_group', true );
                                    }

									if ( ( is_array( $courses ) ) && ( ! empty( $courses ) ) ) {
										foreach( $courses as $course_id ) {
                                            $this->update_course_access( $payment_id, $user_id, $course_id, $remove );
										}
									}

                                    if ( ( is_array( $groups ) ) && ( ! empty( $groups ) ) ) {
                                        foreach( $groups as $group_id ) {
                                            $this->update_group_access( $payment_id, $user_id, $group_id, $remove );
                                        }
                                    }
								}
							}
						}
					}
				}
			}
        }

        /**
         * Update user course access
         *
         * @param  int    $user_id   User ID
         * @param  int    $course_id Course ID
         * @return void
         */
        public function update_course_access( $transaction_id, $user_id, $course_id, $remove = false )
        {
            if ( ! $remove ) {
                $counter = $this->increment_course_access_counter( $course_id, $user_id, $transaction_id );

                ld_update_course_access( (int) $user_id, (int) $course_id );
            } else {
                $counter = $this->decrement_course_access_counter( $course_id, $user_id, $transaction_id );

                if ( empty( $counter[ $course_id ] ) ) {
                    ld_update_course_access( (int) $user_id, (int) $course_id, $remove );
                }
            }
        }

        /**
         * Update user group access
         *
         * @param  int    $user_id   User ID
         * @param  int    $group_id Course ID
         * @return void
         */
        public function update_group_access( $transaction_id, $user_id, $group_id, $remove = false )
        {
            if ( ! $remove ) {
                $counter = $this->increment_course_access_counter( $group_id, $user_id, $transaction_id );

                ld_update_group_access( (int) $user_id, (int) $group_id );
            } else {
                $counter = $this->decrement_course_access_counter( $group_id, $user_id, $transaction_id );

                if ( empty( $counter[ $group_id ] ) ) {
                    ld_update_group_access( (int) $user_id, (int) $group_id, $remove );
                }
            }
        }

        /**
         * Give course access when user complete a purchase
         *
         * @param  int  $payment_id   ID of a transaction
         * @since  1.1.0
         */
        public function complete_purchase( $payment_id ) {
            $this->update_access( $payment_id );
        }

        /**
         * Update access by payment ID
         *
         * @param int    $payment_id
         * @return void
         */
        public function update_access_by_payment_id( $payment_id )
        {
            $payment = edd_get_payment( $payment_id );

            if ( in_array( $payment->status, array( 'publish', 'complete', 'completed' ), true ) ) {
                $this->update_access( $payment_id );
            } else {
                $this->update_access( $payment_id, true );
            }
        }

        /**
         * Give course access when payment is updated to complete on payment edit screen
         *
         * @param  int  $payment_id   ID of a payment
         * @since  1.1.0
         */
        public function updated_edited_purchase( $payment_id ) {
            $payment = new EDD_Payment( $payment_id );

            if ( $payment->status != 'publish' && $payment->status != 'edd_subscription' ) {
                return;
            }

            $this->update_access( $payment_id );
        }

        /**
         * Remove course access when payment is refunded
         *
         * @param  object $payment EDD_Payment object
         * @since  1.1.0
         */
        public function refund_payment( $payment ) {
            $transaction_id = $payment->transaction_id;

            $this->update_access( $transaction_id, $remove = true );
        }

        /**
         * Update course access when subscription status changes.
         *
         * @param  string $old_status   Old status.
         * @param  string $new_status   New status.
         * @param  object $subscription EDD_Subscription object.
         * @since  1.1.0
         */
        public function edd_subscription_status_change( $old_status, $new_status, $subscription )
        {
            $checked_statuses = array( 'active', 'completed' );

            if ( ! in_array( $old_status, $checked_statuses ) && in_array( $new_status, $checked_statuses ) ) {
                $transaction_id = $subscription->parent_payment_id;

                $this->update_access( $transaction_id );
            }
        }

        /**
         * Remove course access when subscription is cancelled
         *
         * @param  int    $subscription_id  ID of a subscription
         * @param  object $subscription     EDD_Subscription object
         * @since  1.1.0
         */
        public function cancel_subscription( $subscription_id, $subscription ) {
            $transaction_id = $subscription->parent_payment_id;

            if ( ! $subscription->is_expired() ) {
                return;
            }

            $this->update_access( $transaction_id, $remove = true );
        }

        /**
         * Remove course access when subscription is failing
         *
         * @param  int    $subscription_id  ID of a subscription
         * @param  object $subscription     EDD_Subscription object
         * @since  1.1.0
         */
        public function cancel_subscription_on_failing_subscription( $subscription_id, $subscription ) {
            $transaction_id = $subscription->parent_payment_id;

            $this->update_access( $transaction_id, $remove = true );
        }

        /**
         * Handle subscription expiration
         *
         * @param int              $subscription_id
         * @param EDD_Subscription $subscription
         * @return void
         */
        public function subscription_expired( $subscription_id, $subscription )
        {
            if ( apply_filters( 'learndash_edd_remove_access_on_subscription_expiration', true, $subscription ) ) {
                $transaction_id = $subscription->parent_payment_id;

                $this->update_access( $transaction_id, $remove = true );
            }
        }

        /**
         * Handle subscription completion
         *
         * @param int              $subscription_id
         * @param EDD_Subscription $subscription
         * @return void
         */
        public function subscription_completed( $subscription_id, $subscription )
        {
            if ( apply_filters( 'learndash_edd_remove_access_on_subscription_completion', false, $subscription ) ) {
                $transaction_id = $subscription->parent_payment_id;

                $this->update_access( $transaction_id, $remove = true );
            }
        }

        /**
         * Remove user course access when EDD payment status is updated to
         * other than completed and renewal
         *
         * @param  int    $payment_id ID of the payment
         * @param  string $status     New status
         * @param  string $old_status Old status
         */
        public function remove_access_on_payment_update( $payment_id, $new_status, $old_status ) {
            if ( in_array( $old_status, array( 'complete', 'publish' ) )  && ! in_array( $new_status, array( 'complete', 'publish', 'edd_subscription' ) ) ) {
                $this->update_access( $payment_id, $remove = true );
            }
        }

        /**
         * Remove user course access when EDD payment status is deleted
         *
         * @param  int    $payment_id ID of the payment
         */
        public function remove_access_on_payment_delete( $payment_id ) {
            $payment = new EDD_Payment( $payment_id );
            if ( $payment->status == 'complete' ) {
                $this->update_access( $payment_id, $remove = true );
            }
        }

        /**
         * Check logged in user expired subscription
         *
         * Remove LearnDash course and group access if a user has cancelled
         * subscription that has passed its expiry date.
         *
         * @return void
         */
        public function check_user_expired_subscription()
        {
            if ( class_exists( 'EDD_Recurring_Subscriber' ) ) {
                $user_id = get_current_user_id();

                if ( $user_id > 0 ) {
                    $checked = get_transient( 'learndash_edd_check_expired_subscription_' . $user_id );

                    if ( ! $checked ) {
                        $subscriber = new EDD_Recurring_Subscriber( $user_id, true );

                        $subscriptions = $subscriber->get_subscriptions( 0, [ 'cancelled' ] );

                        foreach ( $subscriptions as $subscription ) {
                            if ( $subscription->is_expired() ) {
                                $transaction_id = $subscription->parent_payment_id;

                                $this->update_access( $transaction_id, $remove = true );
                            }
                        }

                        set_transient( 'learndash_edd_check_expired_subscription_' . $user_id, time(), DAY_IN_SECONDS );
                    }
                }
            }
        }

        /**
         * Add enrolled course record to a user
         *
         * @param int $course_id ID of a course
         * @param int $user_id   ID of a user
         * @param int $payment_id  ID of an order
         */
        private function increment_course_access_counter( $course_id, $user_id, $payment_id )
        {
            $courses = $this->get_courses_access_counter( $user_id );

            if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
                $courses[ $course_id ] = array();
            }

            if ( ! isset( $courses[ $course_id ] ) || ( isset( $courses[ $course_id] ) && array_search( $payment_id, $courses[ $course_id ] ) === false ) ) {
                // Add order ID to course access counter
                $courses[ $course_id ][] = $payment_id;
            }

            update_user_meta( $user_id, '_learndash_edd_enrolled_courses_access_counter', $courses );

            return $courses;
        }

        /**
         * Delete enrolled course record from a user
         *
         * @param int $course_id ID of a course
         * @param int $user_id   ID of a user
         * @param int $payment_id  ID of an order
         */
        private function decrement_course_access_counter( $course_id, $user_id, $payment_id )
        {
            $courses = $this->get_courses_access_counter( $user_id );

            if ( isset( $courses[ $course_id ] ) && ! is_array( $courses[ $course_id ] ) ) {
                $courses[ $course_id ] = array();
            }

            if ( isset( $courses[ $course_id ] ) ) {
                $keys = array_keys( $courses[ $course_id ], $payment_id );
                if ( is_array( $keys ) ) {
                    foreach ( $keys as $key ) {
                        unset( $courses[ $course_id ][ $key ] );
                    }
                }
            }

            update_user_meta( $user_id, '_learndash_edd_enrolled_courses_access_counter', $courses );

            return $courses;
        }

        /**
         * Reset course access counter
         *
         * @param  int    $course_id Course ID
         * @param  int    $user_id   User ID
         * @return array
         */
        private function reset_course_access_counter( $course_id, $user_id ) {
            $courses = $this->get_courses_access_counter( $user_id );

            if ( isset( $courses[ $course_id ] ) ) {
                unset( $courses[ $course_id ] );
            }

            update_user_meta( $user_id, '_learndash_edd_enrolled_courses_access_counter', $courses );

            return $courses;
        }

        /**
         * Get user enrolled course access counter
         *
         * @param  int $user_id ID of a user
         * @return array        Course access counter array
         */
        private function get_courses_access_counter( $user_id )
        {
            $courses = get_user_meta( $user_id, '_learndash_edd_enrolled_courses_access_counter', true );

            if ( ! empty( $courses ) ) {
                $courses = maybe_unserialize( $courses );
            } else {
                $courses = array();
            }

            return $courses;
        }

        /**
         * Cron job: update existing user course access when product course/group settings updated
         *
         * @todo Update course access based on variable product settings
         */
        public static function cron_update_course_access()
        {
            // Get course update queue
            $updates = get_option( 'learndash_edd_course_access_update', array() );

            foreach ( $updates as $download_id => $update ) {
                $per_batch = apply_filters( 'learndash_edd_cron_update_course_access_per_batch', 100 );
                $per_batch = intval( $per_batch / 2 );
                $batch = $update['batch'] ?? 1;
                $offset = ( $batch - 1 ) * $per_batch;

                $download = new EDD_Download( $download_id );

                if ( isset( $update['type'] ) && $update['type'] == 'variable' ) {
                    $old_courses = $update['old_courses'] ?? [];
                    $new_courses = $update['new_courses'] ?? [];

                    $old_groups = $update['old_groups'] ?? [];
                    $new_groups = $update['new_groups'] ?? [];

                    $removed_courses_variable = [];
                    $added_courses_variable = [];

                    $removed_groups_variable = [];
                    $added_groups_variable = [];

                    foreach ( $download->get_prices() as $price ) {
                        $key = $price['index'];

                        $old_courses[ $key ] = $old_courses[ $key ] ?? [];
                        $new_courses[ $key ] = $new_courses[ $key ] ?? [];
                        $old_groups[ $key ]  = $old_groups[ $key ] ?? [];
                        $new_groups[ $key ]  = $new_groups[ $key ] ?? [];

                        $removed_courses_variable[ $key ] = array_diff( (array) $old_courses[ $key ], (array) $new_courses[ $key ] );

                        $added_courses_variable[ $key ] = array_diff( (array) $new_courses[ $key ], (array) $old_courses[ $key ] );

                        $removed_groups_variable[ $key ] = array_diff( (array) $old_groups[ $key ], (array) $new_groups[ $key ] );

                        $added_groups_variable[ $key ] = array_diff( (array) $new_groups[ $key ], (array) $old_groups[ $key ] );
                    }
                } else {
                    $old_courses = $update['old_courses'] ?? [];
                    $new_courses = $update['new_courses'] ?? [];
                    $removed_courses = array_diff( (array) $old_courses, (array) $new_courses );
                    $added_courses = array_diff( (array) $new_courses, (array) $old_courses );

                    $old_groups = $update['old_groups'] ?? [];
                    $new_groups = $update['new_groups'] ?? [];
                    $removed_groups = array_diff( (array) $old_groups, (array) $new_groups );
                    $added_groups = array_diff( (array) $new_groups, (array) $old_groups );
                }

                 // Payments
                 $payments = edd_get_payments( [
                    'offset' => $offset,
                    'number' => $per_batch,
                    'status' => 'publish',
                    'output' => 'payments',
                    'download' => $download_id,
                ] );

                // Remove or give access for each transaction
                foreach ( $payments as $payment ) {
                    if ( empty( $payment->get_meta( '_edd_subscription_payment' ) ) ) {
                        $downloads = $payment->downloads;

                        $downloads = array_filter( $downloads, function( $value ) use ( $download_id ) {
                            return $value['id'] == $download_id;
                        } );

                        if ( isset( $update['type'] ) && $update['type'] == 'variable' ) {
                            foreach ( $downloads as $download ) {
                                $removed_courses = $removed_courses_variable[ $download['options']['price_id'] ];
                                $added_courses = $added_courses_variable[ $download['options']['price_id'] ];

                                $removed_groups = $removed_groups_variable[ $download['options']['price_id'] ];
                                $added_groups = $added_groups_variable[ $download['options']['price_id'] ];

                                break;
                            }
                        }

                        foreach ( $removed_courses as $course_id ) {
                            learndash_edd()->update_course_access( $payment->ID, $payment->user_id, $course_id, $remove = true );
                        }

                        foreach ( $added_courses as $course_id ) {
                            learndash_edd()->update_course_access( $payment->ID, $payment->user_id, $course_id );
                        }

                        foreach ( $removed_groups as $group_id ) {
                            learndash_edd()->update_group_access( $payment->ID, $payment->user_id, $group_id, $remove = true );
                        }

                        foreach ( $added_groups as $group_id ) {
                            learndash_edd()->update_group_access( $payment->ID, $payment->user_id, $group_id );
                        }
                    }
                }

                // Subscriptions
                $subscriptions = false;
                if ( class_exists( 'EDD_Subscriptions_DB' ) ) {
                    $subscriptions_db = new EDD_Subscriptions_DB();
                    $subscriptions = $subscriptions_db->get_subscriptions( [
                        'number'       => $per_batch,
                        'offset'       => $offset,
                        'status'       => 'active',
                        'product_id'   => $download_id,
                    ] );
                }

                // Remove or give access for each subscription
                if ( is_array( $subscriptions ) && ! empty( $subscriptions ) ) {
                    foreach ( $subscriptions as $subscription ) {
                        $parent = new EDD_Payment( $subscription->parent_payment_id );

                        $downloads = $parent->downloads;

                        $downloads = array_filter( $downloads, function( $value ) use ( $download_id ) {
                            return $value['id'] == $download_id;
                        } );

                        if ( isset( $update['type'] ) && $update['type'] == 'variable' ) {
                            $removed_courses = [];
                            $added_courses = [];

                            $removed_groups = [];
                            $added_groups = [];

                            foreach ( $downloads as $download ) {
                                $removed_courses = $removed_courses[ $download['options']['price_id'] ];
                                $added_courses = $added_courses[ $download['options']['price_id'] ];

                                $removed_groups = $removed_groups[ $download['options']['price_id'] ];
                                $added_groups = $added_groups[ $download['options']['price_id'] ];
                            }
                        }

                        foreach ( $removed_courses as $course_id ) {
                            learndash_edd()->update_course_access( $subscription->parent_payment_id, $parent->user_id, $course_id, $remove = true );
                        }

                        foreach ( $added_courses as $course_id ) {
                            learndash_edd()->update_course_access( $subscription->parent_payment_id, $parent->user_id, $course_id );
                        }

                        foreach ( $removed_groups as $group_id ) {
                            learndash_edd()->update_group_access( $subscription->parent_payment_id, $parent->user_id, $group_id, $remove = true );
                        }

                        foreach ( $added_groups as $group_id ) {
                            learndash_edd()->update_group_access( $subscription->parent_payment_id, $parent->user_id, $group_id );
                        }
                    }
                }

                if ( ! empty( $payments ) || ! empty( $subscriptions ) ) {
                    $updates[ $download_id ]['batch'] = $batch + 1;
                    // Bail, still processing the same download ID
                    break;
                }

                unset( $updates[ $download_id ] );
                // Not necessary to bail since it can handle the next iteration since current iteration processes 0 transaction
            }
            update_option( 'learndash_edd_course_access_update', $updates );
        }

        /**
         * AJAX handler for retroactive process
         *
         * @return void
         */
        public function ajax_retroactive_access()
        {
            if ( ! isset( $_POST['nonce'] ) ) {
                wp_die();
            }

            if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_edd_retroactive_access' ) ) {
                wp_die();
            }

            $step      = intval( $_POST['step'] );
            $per_batch = apply_filters( 'learndash_edd_retroactive_tool_per_batch', 10 );
            $offset    = ( $step - 1 ) * $per_batch;
            $payments_count = edd_count_payments();

            $total = 0;
            foreach ( $payments_count as $type => $count ) {
                $total += $count;
            }

            $payments = edd_get_payments( array(
                'number' => $per_batch,
                'page'   => $step,
                'order'  => 'ASC',
                'output' => 'payments',
            ) );

            foreach ( $payments as $payment ) {
                $is_sub = edd_get_payment_meta( $payment->ID, '_edd_subscription_payment' );

                // Check payment that is a parent of a subscription
                if ( $is_sub && class_exists( 'EDD_Subscriptions_DB' ) ) {
                    $subs_db = new EDD_Subscriptions_DB();
                    $subs = $subs_db->get_subscriptions( [
                        'parent_payment_id'  => $payment->ID,
                        'order'             => 'ASC',
                    ] );

                    if ( ! empty( $subs ) ) {
                        foreach ( $subs as $sub ) {
                            if ( in_array( $sub->get_status(), [ 'completed', 'active' ] ) ) {
                                $this->update_access( $payment->ID );
                            } else {
                                if ( ! $sub->is_expired() ) {
                                    continue;
                                }

                                $this->update_access( $payment->ID, true );
                            }
                        }
                    }

                    continue;
                }

                if ( in_array( $payment->status, [ 'completed', 'complete', 'publish' ] ) ) {
                    $this->update_access( $payment->ID );
                } else {
                    $this->update_access( $payment->ID, true );
                }
            }

            if ( ! empty( $orders ) ) {
                $percentage = number_format( ( ( $offset + $per_batch ) / $total ) * 100, 0 );
                $percentage = $percentage > 100 ? 100 : $percentage;

                $return = array(
                    'step'              => intval( $step + 1 ),
                    'percentage'        => intval( $percentage ),
                );
            } else {
                $return = array(
                    'step' => 'complete',
                );
            }

            echo json_encode( $return );

            wp_die();
        }
    }
}


/**
 * The main function responsible for returning the one true LearnDash_EDD
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      LearnDash_EDD The one true LearnDash_EDD
 */
function learndash_edd() {
    return LearnDash_EDD::instance();
}

learndash_edd();
