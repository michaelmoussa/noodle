<?php

declare (strict_types = 1);

namespace Noodle\Transition\Exception;

use Noodle\Statemachine\Exception;

class TransitionPatternMismatch extends Exception
{
    /**
     * Prepares exception message that includes the mismatched pattern and transition string
     *
     * @param string $transition
     * @param string $pattern
     */
    public function __construct(string $transition, string $pattern)
    {
        parent::__construct(
            sprintf(
                'The provided transition string "%s" does not match the configured pattern: "%s"',
                $transition,
                $pattern
            )
        );
    }
}
