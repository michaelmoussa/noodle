<?php

namespace Noodle\State;

interface CreatesWithName
{
     /**
     * Creates and returns a state with the given name
     *
     * @param string $name
     *
     * @return State
     */
    public static function named(string $name) : State;
}
