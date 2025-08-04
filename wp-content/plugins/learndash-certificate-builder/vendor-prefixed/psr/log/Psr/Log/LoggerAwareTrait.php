<?php
/**
 * @license MIT
 *
 * Modified by learndash on 02-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
