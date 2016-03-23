<?php

declare (strict_types = 1);

namespace Noodle\Listener;

use ArrayObject as Context;
use League\Event\EventInterface as Event;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Transition\Input\Input;

class ChangesState extends InvokableListener
{
    final public function __invoke(Event $event, Stateful $object, Context $context, Input $input, State $nextState)
    {
        $object->setCurrentState($nextState);
    }
}
