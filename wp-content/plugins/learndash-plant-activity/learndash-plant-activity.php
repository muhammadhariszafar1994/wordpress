<?php
/**
 * Plugin Name: LearnDash Plant Activity
 * Description: Adds plant-based gamification activities to LearnDash.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2+
 * Text Domain: learndash-plant-activity
 */

defined( 'ABSPATH' ) || exit;

define( 'LDLMS_PLANT_ACTIVITY_PATH', plugin_dir_path( __FILE__ ) );
define( 'LDLMS_PLANT_ACTIVITY_URL', plugin_dir_url( __FILE__ ) );

require_once LDLMS_PLANT_ACTIVITY_PATH . 'includes/class-ld-plant-activity.php';

add_action( 'plugins_loaded', ['LDLMS_Plant_Activity', 'init'] );