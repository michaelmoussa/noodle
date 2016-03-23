<?php

declare (strict_types = 1);

namespace Noodle\Statemachine\Exception;

use ArrayObject as Context;
use Noodle\State\State;
use Noodle\Statemachine\Exception;
use Noodle\Stateful\Stateful;
use Noodle\Transition\Input\Input;

class StateTransitionFailed extends Exception
{
    /**
     * The default exception message that will be used if none is provided
     *
     * @var string
     */
    const MESSAGE = 'Error occurred while executing a state transition';

    /**
     * The context object shared across state transitions
     *
     * @var Context
     */
    private $context;

    /**
     * The stateful object
     *
     * @var Stateful
     */
    private $object;

    /**
     * The input that triggered the state transition
     *
     * @var Input
     */
    private $input;

    /**
     * The state that would have been achived, had the transition succeeded
     *
     * @var State
     */
    private $nextState;

    /**
     * Constructor
     *
     * @param Input $input
     * @param Stateful $object
     * @param Context $context
     * @param State $nextState
     * @param \Exception $previous (Optional)
     */
    public function __construct(
        Input $input,
        Stateful $object,
        Context $context,
        State $nextState,
        \Exception $previous = null
    ) {
        $message = sprintf(
            'Failed attempting to %s a %s with current state %s',
            $input->getName(),
            get_class($object),
            $object->getCurrentStateName()
        );

        parent::__construct($message, 0, $previous);

        $this->object = $object;
        $this->context = $context;
        $this->input = $input;
        $this->nextState = $nextState;
    }

    /**
     * Returns the shared context object
     *
     * @return Context
     */
    public function getContext() : Context
    {
        return $this->context;
    }

    /**
     * Returns the input that triggered the state transition
     *
     * @return Input
     */
    public function getInput() : Input
    {
        return $this->input;
    }

    public function getNextState() : State
    {
        return $this->nextState;
    }

    /**
     * Returns the stateful object
     *
     * @return Stateful
     */
    public function getObject() : Stateful
    {
        return $this->object;
    }
}
