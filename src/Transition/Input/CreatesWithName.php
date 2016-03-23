<?php

namespace Noodle\Transition\Input;

interface CreatesWithName
{
    /**
     * Creates and returns an input with the given name
     *
     * @param string $name
     *
     * @return Input
     */
    public static function named(string $name) : Input;
}
