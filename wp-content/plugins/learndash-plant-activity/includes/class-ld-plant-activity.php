<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LDLMS_Plant_Activity' ) ) {
    class LDLMS_Plant_Activity {

        public static function init() {
            add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
            add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
            add_action( 'rest_api_init', [ __CLASS__, 'register_rest_api' ] );
        }

        public static function enqueue_assets() {
            wp_enqueue_style(
                'ldlms-plant-activity',
                LDLMS_PLANT_ACTIVITY_URL . 'assets/style.css',
                [],
                '1.0'
            );

            wp_enqueue_script('jquery');

            wp_localize_script('jquery', 'wpData', [
                'nonce'    => wp_create_nonce('wp_rest'),
                'rest_url' => esc_url_raw(rest_url('plant-activity/create')),
            ]);

            add_action('wp_print_footer_scripts', function () {
                ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const data = {
                            course_id: 123,
                            user_id: 5,
                            post_id: 77,
                            activity_type: 'plant_activity',
                            activity_action: 'insert',
                            activity_status: true,
                            activity_started: '2025-08-04 10:00:00',
                            activity_completed: '2025-08-04 10:15:00',
                            activity_meta: {
                                score: 95,
                                correct: 9,
                                questions: 10
                            }
                        };

                        fetch(wpData.rest_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': wpData.nonce
                            },
                            body: JSON.stringify(data)
                        })
                        .then(res => res.json())
                        .then(response => console.log('Success:', response))
                        .catch(error => console.error('Error:', error));
                    });
                </script>
                <?php
            });
        }

        public static function add_admin_menu() {
            add_submenu_page(
                'learndash-lms',
                'Plant Activity',
                'Plant Activity',
                'manage_options',
                'ldlms-plant-activity',
                [ __CLASS__, 'render_admin_page' ]
            );
        }

        public static function render_admin_page() {
            ?>
            <div class="wrap">
                <h1>🌱 LearnDash Plant Activity</h1>
                <?php
                    $user_id = get_current_user_id();
                    $plants = get_user_meta( $user_id, '_ld_virtual_plants', true );
                    echo '<p><strong>Total Plants:</strong> ' . intval( $plants ) . '</p>';
                ?>
            </div>
            <?php
        }

        public static function register_rest_api() {
            register_rest_route('plant-activity', '/create', [
                'methods' => 'POST',
                'callback' => [ __CLASS__, 'handle_rest_request' ],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                }
            ]);

        }

        public static function handle_rest_request( WP_REST_Request $request ) {
            $data = $request->get_json_params();
            
            return rest_ensure_response([
                'success'  => true,
                'message'  => 'Plant activity recorded',
                'received' => $data,
            ]);
        }
    }

    LDLMS_Plant_Activity::init();
}