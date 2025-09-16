<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    LD_Mux_streaming
 * @subpackage LD_Mux_streaming/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    LD_Mux_streaming
 * @subpackage LD_Mux_streaming/includes
 * @author     Your Name <email@example.com>
 */
class LD_Mux_streaming_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		flush_rewrite_rules();
	}
}