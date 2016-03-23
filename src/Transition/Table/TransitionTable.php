<?php

namespace Noodle\Transition\Table;

use Noodle\State\State;
use Noodle\Transition\Input\Input;

interface TransitionTable
{
     /**
     * Returns the state that would result from applying the provided input
     * to an object with the provided current state.
     *
     * @param State $currentState
     * @param Input $input
     *
     * @return State
     */
    public function resolve(State $currentState, Input $input) : State;
}
