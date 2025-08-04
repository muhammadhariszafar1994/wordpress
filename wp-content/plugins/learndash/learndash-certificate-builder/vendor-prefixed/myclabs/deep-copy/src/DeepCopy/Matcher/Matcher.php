<?php
/**
 * @license MIT
 *
 * Modified by learndash on 02-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\DeepCopy\Matcher;

interface Matcher
{
    /**
     * @param object $object
     * @param string $property
     *
     * @return boolean
     */
    public function matches($object, $property);
}
