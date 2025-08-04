<?php
/**
 * @license MIT
 *
 * Modified by learndash on 24-September-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Learndash\Illuminate\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \StellarWP\Learndash\Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
