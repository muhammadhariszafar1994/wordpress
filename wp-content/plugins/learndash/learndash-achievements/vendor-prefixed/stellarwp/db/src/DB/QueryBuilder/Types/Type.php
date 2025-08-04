<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 30-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder\Types;

use ReflectionClass;

/**
 * @since 1.0.0
 */
abstract class Type {
	/**
	 * Get Defined Types
	 *
	 * @return array
	 */
	public static function getTypes() {
		return ( new ReflectionClass( static::class ) )->getConstants();
	}
}
