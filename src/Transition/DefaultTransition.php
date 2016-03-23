<?php

declare (strict_types = 1);

namespace Noodle\Transition;

use Noodle\State\State;
use Noodle\Transition\Input\Input;

final class DefaultTransition implements CreateableFromPattern, Transition
{
    use CreatesFromPattern;

    /**
     * @var State
     */
    private $currentState;

    /**
     * @var Input
     */
    private $input;

    /**
     * @var State
     */
    private $nextState;

    public function __construct(State $currentState, Input $input, State $nextState)
    {
        $this->currentState = $currentState;
        $this->input = $input;
        $this->nextState = $nextState;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentState() : State
    {
        return $this->currentState;
    }

    /**
     * {@inheritdoc}
     */
    public function getInput() : Input
    {
        return $this->input;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextState() : State
    {
        return $this->nextState;
    }
}
