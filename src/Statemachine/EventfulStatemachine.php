<?php

declare (strict_types = 1);

namespace Noodle\Statemachine;

use ArrayObject as Context;
use League\Event\Emitter;
use League\Event\ListenerInterface as Listener;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Transition\Input\Input;

interface EventfulStatemachine
{
    /**
     * Adds a listener that listens for the "after applying input to state" event. This is
     * where logic that should take place after the object has been updated with its new
     * state should take place, such as persisting it to a database or doing any kind of
     * logging.
     *
     * @param Input $input
     * @param State $currentState
     * @param Listener $listener
     * @param int $priority
     *
     * @return void
     */
    public function after(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL);

    /**
     * Adds a listener that listens for the "before applying input to state" event. This is
     * where any preconditions for state transition would be evaluated and would be the place
     * to stop an otherwise valid transition from taking place.
     *
     * @param Input $input
     * @param State $currentState
     * @param Listener $listener
     * @param int $priority
     *
     * @return void
     */
    public function before(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL);

    /**
     * Adds a listener that listens for the "on apply input to state" event. Generally,
     * this is when the object's state would be updated and any other related logic would
     * be executed.
     *
     * @param Input $input
     * @param State $currentState
     * @param Listener $listener
     * @param int $priority
     *
     * @return void
     */
    public function on(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL);

    /**
     * Evaluates whether the an input is valid for an object in its current state, then
     * emits the appropriate events.
     *
     * @param Input $input
     * @param Stateful $object
     * @param Context $context
     *
     * @return void
     */
    public function trigger(Input $input, Stateful $object, Context $context = null);
}
