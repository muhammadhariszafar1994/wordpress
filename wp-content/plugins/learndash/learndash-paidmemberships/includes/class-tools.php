<?php
/**
* Tools class
*/
class Learndash_Paidmemberships_Tools
{
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'retroactive_access_menu' ), 15 );
        add_action( 'admin_notices', array( $this, 'retroactive_access_notice' ) );

        add_action( 'wp_ajax_learndash_paidmemberships_retroactive', array( $this, 'retroactive_access_process' ) );
        add_action( 'admin_head', array( $this, 'retroactive_access_page_style' ) );
    }

    public function retroactive_access_menu()
    {
        add_submenu_page( 'learndash_paidmemberships_not_exists', __( 'LearnDash Memberpress', 'learndash-paidmemberships' ), __( 'LearnDash Memberpress', 'learndash-paidmemberships' ), 'manage_options', 'learndash-paidmemberships', array( $this, 'learndash_paidmemberships_page') );

        // Add submenu to PMPro menu
        add_submenu_page( 'pmpro-dashboard', __( 'LearnDash LMS - Paid Memberships Pro Settings', 'learndash-paidmemberships' ), 'LearnDash', 'manage_options', 'learndash-pmpro', array( $this, 'learndash_paidmemberships_settings_page' ) );
    }

    public function retroactive_access_notice()
    {
        global $wpdb;

        if ( isset( $_GET['page'] ) && $_GET['page'] == 'learndash-paidmemberships' ) {
            return;
        }

        $options = get_option( 'learndash_paidmemberships', array() );

        if ( isset( $options['retroactive_access'] ) && $options['retroactive_access'] == 1 ) {
            return;
        }

        $orders_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pmpro_membership_orders" );

        if ( $orders_count < 1 ) {
            return;
        }

        ?>

        <div id="message" class="notice notice-warning">
            <p><?php printf( __( 'After configuring LearnDash - Paid Memberships Pro integration settings, please <a href="%s">click here</a> to start retroactive member access process.', 'learndash-paidmemberships' ), 'admin.php?page=learndash-paidmemberships&action=retroactive_access&_wpnonce=' . wp_create_nonce( 'learndash_paidmemberships' ) ); ?></p>
        </div>

        <?php
    }

    public function learndash_paidmemberships_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e( 'LearnDash Integration Settings', 'learndash-paidmemberships' ) ?></h1>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e( 'Retroactive Tool', 'learndash-paidmemberships' ) ?></th>
                        <td><a class="button button-secondary" href="<?php echo add_query_arg( array( 'page' => 'learndash-paidmemberships', 'action' => 'retroactive_access', '_wpnonce' => wp_create_nonce( 'learndash_paidmemberships' ) ), admin_url( 'admin.php' ) ) ?>"><?php _e( 'Run', 'learndash-paidmemberships' ) ?></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function learndash_paidmemberships_page()
    {
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'retroactive_access' )
        {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'learndash_paidmemberships' ) ) {
                return;
            }

            ?>

            <div class="wrap">
                <div class="learndash-paidmemberships-message">
                    <p><?php _e( 'The process is being done. Please do not close this browser until the process finishes.', 'learndash-paidmemberships' ); ?></p>
                </div>
                <div class="learndash-paidmemberships-progress-bar-wrapper">
                    <div class="learndash-paidmemberships-spinner"></div>
                    <div class="learndash-paidmemberships-progress-bar-inner-wrapper">
                        <div class="learndash-paidmemberships-progress-bar"></div>
                        </div>
                    <div class="clear"></div>
                </div>
            </div>

            <script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    $( window ).load( function() {
                        process_step( 1 );
                    } );

                    function process_step( step ) {
                        $.ajax( {
                            url: ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'action': 'learndash_paidmemberships_retroactive',
                                'step': step,
                            },
                            success: function( response ) {
                                console.log(response);
                                if ( 'done' == response.step ) {
                                    $( '.learndash-paidmemberships-progress-bar' ).animate( {
                                        width: response.percentage + '%' },
                                        50, function() {
                                        // Animation complete.
                                    } );

                                    setTimeout( function() {
                                        $( '.learndash-paidmemberships-message' ).remove();
                                        $( '.learndash-paidmemberships-progress-bar' ).remove();
                                        $( '.learndash-paidmemberships-spinner' ).remove();

                                        $( '.learndash-paidmemberships-progress-bar-wrapper' ).html( '<p>' + '<?php _e( 'The process is complete.', 'learndash-paidmemberships' ); ?>' + '</p>');

                                        location.href = '<?php echo admin_url( 'admin.php?page=pmpro-dashboard' ); ?>';
                                    }, 2000 );
                                } else {
                                    $( '.learndash-paidmemberships-progress-bar' ).animate( {
                                        width: response.percentage + '%' },
                                        50, function() {
                                        // Animation complete.
                                    } );

                                    process_step( parseInt( response.step ) );
                                }
                            }
                        } )
                        .fail( function( response ) {
                            if ( window.console && window.console.log ) {
                                console.log( response );
                            }
                        } );
                    }
                });
            </script>

            <?php
        }
    }

    public function retroactive_access_process()
    {
        global $wpdb;

        $per_batch   = 50;
        $step        = intval( $_POST['step'] );
        $offset      = $per_batch * ( $step - 1 );
        $members_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->pmpro_memberships_users}" );
        $members_percentage = ! empty( $members_total ) ? ( $step - 1 ) / ceil( $members_total / $per_batch ) * 100 : 100;
        
        $percentage  = $members_percentage / 2;

        $query = $wpdb->prepare( "SELECT id, user_id, membership_id, status FROM {$wpdb->pmpro_memberships_users} LIMIT %d OFFSET %d", $per_batch, $offset );

        $members = $wpdb->get_results( $query );

        foreach ( $members as $member ) {
            $user = get_user_by( 'id', $member->user_id ); 
            if ( $member->status == 'active' && $user ) {
                Learndash_Paidmemberships::update_object_access( $member->membership_id, $member->user_id );
            } elseif ( $member->status != 'active' && $user ) {
                Learndash_Paidmemberships::update_object_access( $member->membership_id, $member->user_id, true );
            }
        }

        if ( ! empty( $members ) ) {
            $step += 1;
        } else {
            $step = 'done';
            $percentage = 100;
        }

        $options = get_option( 'learndash_paidmemberships', array() );
        $options['retroactive_access'] = 1;
        update_option( 'learndash_paidmemberships', $options );

        echo json_encode( array( 'percentage' => $percentage, 'step' => $step ) );

        wp_die();
    }

    public function retroactive_access_page_style()
    {
        if ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'retroactive_access' ) {
            return;
        }

        ?>

        <style type="text/css">
            .learndash-paidmemberships-progress-bar-wrapper {
                border: 1px solid #c6c6bb;
                padding: 10px;
            }

            .learndash-paidmemberships-progress-bar-inner-wrapper {
                padding: 0;
                margin: 5px 0 0;
                background-color: #fff;
                display: block;
                float: left;
                line-height: 1;
                width: 94.8%;
            }

            .learndash-paidmemberships-progress-bar {
                background-color: #3498db;
                border: 1px solid #fff;
                height: 15px;
                width: 0;
                max-width: 100%;
            }

            .learndash-paidmemberships-spinner {
                border: 4px solid #f3f3f3; /* Light grey */
                border-top: 4px solid #3498db; /* Blue */
                background-color: #fff;
                border-radius: 50%;
                margin-right: 10px;
                width: 20px;
                height: 20px;
                animation: spin 1s linear infinite;
                float: left;
            }

            @keyframes spin {
                0% { transform: rotate( 0deg ); }
                100% { transform: rotate( 360deg ); }
            }

            .clear {
                clear: both;
            }
        </style>

        <?php
    }
}

new Learndash_Paidmemberships_Tools();