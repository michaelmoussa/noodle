<?php

declare (strict_types = 1);

namespace Noodle\Listener;

use ArrayObject as Context;
use League\Event\EventInterface as Event;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Statemachine\Exception\StateTransitionFailed;
use Noodle\Transition\Input\Input;

class ReportsTransitionFailures extends InvokableListener
{
    /**
     * Throws an exception with details on the failed transition
     *
     * @param Event $event
     * @param Stateful $object
     * @param Context $context
     * @param Input $input
     * @param State $nextState
     */
    final public function __invoke(Event $event, Stateful $object, Context $context, Input $input, State $nextState)
    {
        throw new StateTransitionFailed($input, $object, $context, $nextState);
    }
}
