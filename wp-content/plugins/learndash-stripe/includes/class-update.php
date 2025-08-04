<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Update class
 */
class LearnDash_Stripe_Update
{
    public $screen;

    public $update;

    public $current_version;

    public $saved_version;
    
    public function __construct()
    {   
        add_action( 'current_screen', function() {
            $this->screen = get_current_screen();
        } );

        $this->update = get_option( 'learndash_stripe_update', [] );
        $this->saved_version   = get_option( 'learndash_stripe_version' );
        $this->current_version = LEARNDASH_STRIPE_VERSION;
        if ( version_compare( $this->current_version, $this->saved_version , '>' ) || ! $this->saved_version ) {
            $method_name = 'upgrade_' . str_replace( '.', '_', $this->current_version );
            if ( method_exists( $this, $method_name ) ) {
                $this->$method_name();
            }
        }
    }

    public function upgrade_1_8_0_1()
    {
        add_action( 'admin_notices', function() {
            if ( $this->screen->base === 'edit' && $this->screen->post_type === 'sfwd-transactions' ) {
                ?>
                <div class="notice notice-warning update-1-8-0-1">
                    <p><?php _e( '<a href="#" class="ld-stripe-update-1-8-0-1">Click here</a> to fix LearnDash Stripe transaction records. Please keep this page open until the process finishes.', 'learndash-stripe' ) ?></p>
                </div>
                <?php
            }
        } );

        add_action( 'admin_head', function() {
            if ( $this->screen->base === 'edit' && $this->screen->post_type === 'sfwd-transactions' ) {
                ?>
                <style>
                    #update-status {
                        margin: 5px;
                    }

                    .ld-stripe-spinner {
                        display: inline-block;
                        margin-left: 10px;
                    }

                    .ld-stripe-spinner:after {
                        content: " ";
                        display: block;
                        width: 5px;
                        height: 5px;
                        padding: 3px;
                        border-radius: 50%;
                        border: 2px solid #333;
                        border-color: #333 transparent #333 transparent;
                        animation: ld-stripe-spinner 1.2s linear infinite;
                    }

                    @keyframes ld-stripe-spinner {
                        0% {
                            transform: rotate(0deg);
                        }
                        100% {
                            transform: rotate(360deg);
                        }
                    }
                </style>
                <?php
            }
        } );

        add_action( 'admin_footer', function() {
            if ( $this->screen->base === 'edit' && $this->screen->post_type === 'sfwd-transactions' ) {
                ?>
                    <script type="text/javascript">
                        (function() {
                            jQuery( document ).ready( function( $ ) {
                                var nonce = '<?php echo wp_create_nonce( 'ld_stripe_fix_transaction_records_1_8_0_1' ); ?>',
                                    text = {
                                        'status' : '<?php _e( 'Progress', 'learndash-stripe' ) ?>',
                                        'complete' : '<?php _e( 'Complete', 'learndash-stripe' ) ?>',
                                    };

                                var init_update_1_8_0_1 = function() {
                                    $( document ).on( 'click', '.ld-stripe-update-1-8-0-1', function( e ) {
                                        e.preventDefault();
                                        $( this ).parent( 'p' ).append( '<span class="recursive-status" id="update-status"></span>');
                                        $( '#update-status' ).text( text.status + ': 0%' );
                                        $( this ).parent( 'p' ).append( '<span class="ld-stripe-spinner"></span>');

                                        fix_transaction_records_1_8_0_1();
                                    });
                                };

                                var fix_transaction_records_1_8_0_1 = function( step = 1 ) {
                                    $.ajax({
                                        url: ajaxurl,
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            action: 'ld_stripe_fix_transaction_records_1_8_0_1',
                                            nonce: nonce,
                                            step: step,
                                        },
                                    }).done( function( data ) {
                                        console.log( data );
                                        if ( typeof( data ) != 'undefined' ) {
                                            if ( data.step !== 'complete' ) {
                                                $( '#update-status' ).text( text.status + ': ' + data.percentage + '%' );
                                                fix_transaction_records_1_8_0_1( data.step );
                                            } else {
                                                // done
                                                $( '.ld-stripe-spinner' ).remove();
                                                $( '#update-status' ).text( text.status + ': ' + text.complete );
                                            }
                                        }
                                    });
                                }

                                init_update_1_8_0_1();
                            });
                        })();
                    </script>
                <?php
            }
        } );

        add_action( 'wp_ajax_ld_stripe_fix_transaction_records_1_8_0_1', function() {
            if ( ! isset( $_POST['nonce'] ) ) {
                wp_die();
            }

            if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_stripe_fix_transaction_records_1_8_0_1' ) ) {
                wp_die();
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die();
            }

            $step               = intval( $_POST['step'] );
            $per_batch          = 10;
            $offset             = ( $step - 1 ) * $per_batch;
            $total              = wp_count_posts( 'sfwd-transactions' )->publish;
            $transactions       = get_posts( [
                'post_type'      => 'sfwd-transactions',
                'posts_per_page' => $per_batch,
                'offset'         => $offset,
            ] );

            if ( ! empty( $transactions ) ) {
                foreach ( $transactions as $transaction ) {
                    $nonce = get_post_meta( $transaction->ID, 'stripe_nonce', true );
                    $payment_intent = get_post_meta( $transaction->ID, 'stripe_payment_intent', true );

                    // Only update Stripe new checkout transactions
                    if ( empty( $nonce ) && ! empty( $payment_intent ) ) {
                        update_post_meta( $transaction->ID, 'stripe_nonce', 'n/a' );

                        $price = get_post_meta( $transaction->ID, 'amount', true );
                        if ( ! empty( $price ) ) {
                            update_post_meta( $transaction->ID, 'stripe_price', $price );
                            delete_post_meta( $transaction->ID, 'amount' );
                        }

                        $currency = get_post_meta( $transaction->ID, 'currency', true );
                        if ( ! empty( $currency ) ) {
                            update_post_meta( $transaction->ID, 'stripe_currency', $currency );
                            delete_post_meta( $transaction->ID, 'currency' );
                        }
                    }

                }

                $percentage = number_format( ( ( $offset + $per_batch ) / $total ) * 100, 0 );
                $percentage = $percentage > 100 ? 100 : $percentage;

                $return = array(
                    'step'              => intval( $step + 1 ),
                    'percentage'        => floatval( $percentage ),
                );
            } else {
                $this->update[ $this->current_version ] = 1; 
                update_option( 'learndash_stripe_update', $this->update, false );
                update_option( 'learndash_stripe_version', $this->current_version, false );

                $return = array(
                    'step' => 'complete',
                );
            }

            echo json_encode( $return );
            wp_die();
        } );
    }
}

global $ld_stripe_update;
$ld_stripe_update = new LearnDash_Stripe_Update();