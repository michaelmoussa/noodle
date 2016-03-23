<?php

namespace Noodle\Transition;

use Noodle\State\State;
use Noodle\Transition\Input\Input;

interface Transition
{
    /**
     * Returns the state from which this transition begins
     *
     * @return State
     */
    public function getCurrentState() : State;

    /**
     * Returns the input that triggers this transition
     *
     * @return Input
     */
    public function getInput() : Input;

    /**
     * Returns the state in which this transition results
     *
     * @return State
     */
    public function getNextState() : State;
}
