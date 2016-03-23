<?php

declare (strict_types = 1);

namespace Noodle\Transition\Table\Exception;

use Noodle\State\State;
use Noodle\Statemachine\Exception;
use Noodle\Transition\Input\Input;

class InvalidInputForState extends Exception
{
    /**
     * @var Input
     */
    private $input;

    /**
     * @var State
     */
    private $state;

    /**
     * Prepares exception message that includes the invalid input and state
     *
     * @param Input $input
     * @param State $state
     */
    public function __construct(Input $input, State $state)
    {
        $this->input = $input;
        $this->state = $state;

        parent::__construct(sprintf('Cannot %s a %s object', $input->getName(), $state->getName()));
    }

    /**
     * Returns the input that was attempted.
     *
     * @return Input
     */
    public function getInput() : Input
    {
        return $this->input;
    }

    /**
     * Returns what the object's current state was.
     *
     * @return State
     */
    public function getState() : State
    {
        return $this->state;
    }
}
