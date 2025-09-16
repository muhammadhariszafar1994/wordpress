<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LD_Mux_Streaming
 * @subpackage LD_Mux_Streaming/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LD_Mux_Streaming
 * @subpackage LD_Mux_Streaming/admin
 * @author     Your Name <email@example.com>
 */
class LD_Mux_Streaming_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ld_mux_streaming    The ID of this plugin.
	 */
	private $ld_mux_streaming;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $ld_mux_streaming       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $token_id;
    private $token_secret;

	public function __construct( $ld_mux_streaming, $version ) {

		$this->ld_mux_streaming = $ld_mux_streaming;
		$this->version = $version;

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		$this->token_id = get_option('ld_mux_token_id');
        $this->token_secret = get_option('ld_mux_token_secret');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->ld_mux_streaming, plugin_dir_url( __FILE__ ) . 'css/ld-mux-streaming-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	}

	/**
     * Add submenu under LearnDash
     */
    public function add_plugin_admin_menu() {
        add_submenu_page(
            'learndash-lms',                           // Parent slug (LearnDash menu)
            __( 'LD Mux Streaming', 'ld-mux-streaming' ), // Page title
            __( 'LD Mux Streaming', 'ld-mux-streaming' ), // Menu title
            'manage_options',                          // Capability
            $this->plugin_name,                        // Menu slug
            array( $this, 'display_plugin_admin_page' ) // Callback
        );
    }

    /**
     * Render settings form
     */
    public function display_plugin_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'LD Mux Streaming Settings', 'ld-mux-streaming' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'ld_mux_options' );
                do_settings_sections( 'ld-mux-streaming' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting( 'ld_mux_options', 'ld_mux_token_id' );
        register_setting( 'ld_mux_options', 'ld_mux_token_secret' );

        add_settings_section(
            'ld_mux_main_section',
            __( 'Mux API Credentials', 'ld-mux-streaming' ),
            null,
            'ld-mux-streaming'
        );

        add_settings_field(
            'ld_mux_token_id',
            __( 'Mux Token ID', 'ld-mux-streaming' ),
            array( $this, 'field_token_id' ),
            'ld-mux-streaming',
            'ld_mux_main_section'
        );

        add_settings_field(
            'ld_mux_token_secret',
            __( 'Mux Token Secret', 'ld-mux-streaming' ),
            array( $this, 'field_token_secret' ),
            'ld-mux-streaming',
            'ld_mux_main_section'
        );
    }

    public function field_token_id() {
        printf(
            '<input type="text" name="ld_mux_token_id" value="%s" class="regular-text" />',
            esc_attr( get_option( 'ld_mux_token_id' ) )
        );
    }

    public function field_token_secret() {
        printf(
            '<input type="password" name="ld_mux_token_secret" value="%s" class="regular-text" />',
            esc_attr( get_option( 'ld_mux_token_secret' ) )
        );
    }

	private function auth_header() {
        return 'Basic ' . base64_encode($this->token_id . ':' . $this->token_secret);
    }

    public function create_upload() {
        $url = 'https://api.mux.com/video/v1/uploads';
        $args = array(
            'headers' => array(
                'Authorization' => $this->auth_header(),
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'new_asset_settings' => array('playback_policy' => array('public'))
            )),
            'timeout' => 20,
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) return $response;

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            return new WP_Error('mux_api_error', 'Mux API returned ' . $code, array('body' => $body));
        }

        return json_decode($body, true);
    }

    public function ajax_get_upload_url() {
        $result = $this->create_upload();
        if ( is_wp_error($result) ) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        wp_send_json_success($result);
    }

    public function register_rest_routes() {
        register_rest_route('ld-mux-streaming/v1', '/webhook', array(
            'methods'  => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_webhook($request) {
        $payload = $request->get_json_params();
        if (empty($payload)) return new WP_REST_Response(array('error'=>'no payload'), 400);

        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? array();

        if ($type === 'video.asset.ready') {
            $asset_id = $data['id'] ?? null;
            $playback_ids = $data['playback_ids'] ?? null;

            if ($asset_id) {
                $saved = get_option('ld_mux_assets', array());
                $saved[$asset_id] = $data;
                update_option('ld_mux_assets', $saved);
            }
        }

        return new WP_REST_Response(array('status'=>'ok'), 200);
    }
}