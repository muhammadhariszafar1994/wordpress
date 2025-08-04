<?php
/**
 * Sets up deprecated support.
 *
 * @since 1.4.0
 */

defined( 'ABSPATH' ) || die();

add_action( 'after_setup_theme', 'rbm_fh_deprecated_support' );

/**
 * Creates a new RBM_FieldHelpers instance if the deprecated support is enabled.
 *
 * @since 1.4.0
 * @access private
 */
function rbm_fh_deprecated_support() {

	if ( ! defined( 'RBM_FH_DEPRECATED_SUPPORT' ) || RBM_FH_DEPRECATED_SUPPORT !== true ) {

		return;
	}

	global $rbm_fh_deprecated_support;

	$rbm_fh_deprecated_support = new RBM_FieldHelpers();
	
	add_action( 'dbx_post_sidebar', 'rbm_fh_deprecated_save_meta' );
	
}

/**
 * Ensure any deprecated Post Meta fields are saved properly
 * 
 * @since 1.4.13
 * @access private
 */
function rbm_fh_deprecated_save_meta() {
	
	rbm_fh_init_field_group( 'default' );
	
}