<?php

declare (strict_types = 1);

namespace Noodle\Listener;

use ArrayObject as Context;
use League\Event\AbstractListener;
use League\Event\EventInterface;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Transition\Input\Input;
use League\Event\EventInterface as Event;

abstract class InvokableListener extends AbstractListener
{
    /**
     * Executes listener logic
     *
     * @param Event $event
     * @param Stateful $object
     * @param Context $context
     * @param Input $input
     * @param State $nextState
     *
     * @return void
     */
    abstract public function __invoke(Event $event, Stateful $object, Context $context, Input $input, State $nextState);

    /**
     * Proxy call to __invoke(...)
     *
     * @param Event $event
     */
    public function handle(EventInterface $event)
    {
        call_user_func_array($this, func_get_args());
    }
}
