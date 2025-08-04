<?php
/**
 * Field Template: Hook
 *
 * @since 1.5.0
 *
 * @var array $args Field arguments.
 * @var string $name Field name.
 * @var mixed $value Field value.
 */

defined( 'ABSPATH' ) || die();

do_action( "$args[prefix]_$name", $args, $value );
?>