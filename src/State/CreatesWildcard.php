<?php

namespace Noodle\State;

interface CreatesWildcard
{
    /**
     * Creates a "wildcard" state that can be used by statemachine listeners
     * to indicate that an event should be emitted for transitions occurring
     * from any current state.
     *
     * @return State
     */
    public static function any() : State;
}
