<?php

namespace Noodle\Transition\Input;

interface CreatesWildcard
{
    /**
     * Creates a "wildcard" input that can be used by statemachine listeners
     * to indicate that an event should be emitted for transitions occurring
     * due to any input.
     *
     * @return Input
     */
    public static function any() : Input;
}
