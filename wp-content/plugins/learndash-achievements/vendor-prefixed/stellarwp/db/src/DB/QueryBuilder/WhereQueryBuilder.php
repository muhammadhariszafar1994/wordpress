<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 30-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace LearnDash\Achievements\StellarWP\DB\QueryBuilder;

use LearnDash\Achievements\StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 1.0.0
 */
class WhereQueryBuilder {
	use WhereClause;

	/**
	 * @return string[]
	 */
	public function getSQL() {
		return $this->getWhereSQL();
	}
}
