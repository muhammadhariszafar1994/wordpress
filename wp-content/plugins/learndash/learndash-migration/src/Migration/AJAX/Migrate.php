<?php
/**
 * Migrate AJAX module.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 *
 * @package LearnDash\Migration
 */

_deprecated_file(
	__FILE__,
	'1.1.0',
	esc_html(
		LEARNDASH_MIGRATION_PLUGIN_DIR . 'src/App/AJAX/Migrate.php'
	)
);

require_once LEARNDASH_MIGRATION_PLUGIN_DIR . 'src/App/AJAX/Migrate.php';
