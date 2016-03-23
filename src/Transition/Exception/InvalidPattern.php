<?php

declare (strict_types = 1);

namespace Noodle\Transition\Exception;

use Noodle\Statemachine\Exception;

class InvalidPattern extends Exception
{
    /**
     * Prepares exception message that includes the invalid pattern
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct(
            sprintf('The supplied pattern "%s" does not appear to be a valid regular expression', $pattern)
        );
    }
}
