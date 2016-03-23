<?php

namespace Noodle\Stateful;

use Noodle\State\State;

interface Stateful
{
    /**
     * Returns whether or not the Stateful object has a current state
     *
     * @return bool
     */
    public function hasCurrentState() : bool;

    /**
     * Returns the Stateful object's current state
     *
     * @return State
     */
    public function getCurrentState() : State;

    /**
     * Returns the name of the Stateful object's current state
     *
     * @return string
     */
    public function getCurrentStateName() : string;

    /**
     * Sets the Stateful object's current state
     *
     * @param State $state
     *
     * @return void
     */
    public function setCurrentState(State $state);
}
