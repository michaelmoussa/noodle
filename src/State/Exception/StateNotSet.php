<?php

declare (strict_types = 1);

namespace Noodle\State\Exception;

use Noodle\Statemachine\Exception;

class StateNotSet extends Exception
{
    /**
     * {@inheritDoc}
     */
    const MESSAGE = 'Cannot get current state for object because no state is set';
}
