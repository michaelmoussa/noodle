<?php

declare (strict_types = 1);

namespace Noodle\Transition\Input;

interface Input
{
    /**
     * Returns the name of the input
     *
     * @return string
     */
    public function getName() : string;
}
