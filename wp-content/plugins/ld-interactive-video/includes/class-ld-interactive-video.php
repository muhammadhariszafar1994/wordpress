<?php

class LD_Interactive_Video {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'ld-interactive-video';
        $this->version = LD_INTERACTIVE_VIDEO_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ld-interactive-video-admin.php';

        $this->loader = new stdClass(); // or custom loader class if needed
    }

    private function define_admin_hooks() {
        $plugin_admin = new LD_Interactive_Video_Admin($this->plugin_name, $this->version);

//        add_action('add_meta_boxes', [$plugin_admin, 'add_ld_interactive_metabox']);
//        add_action('save_post', [$plugin_admin, 'save_ld_interactive_video']);
        add_action('admin_enqueue_scripts', [$plugin_admin, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$plugin_admin, 'enqueue_scripts']);
    }


    public function run() {
        // If you're using a loader class, call $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
