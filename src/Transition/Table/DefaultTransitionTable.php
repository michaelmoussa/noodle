<?php

declare (strict_types = 1);

namespace Noodle\Transition\Table;

use Noodle\State\State;
use Noodle\Transition\Input\Input;
use Noodle\Transition\Table\Exception\InvalidInputForState;
use Noodle\Transition\Transition;

final class DefaultTransitionTable implements TransitionTable
{
    /**
     * @var array
     */
    private $transitions = [];

    /**
     * Constructor
     *
     * @param \Noodle\Transition\Transition[] ...$transitions
     */
    public function __construct(Transition ...$transitions)
    {
        foreach ($transitions as $transition) {
            $actionKey = $this->getActionKey($transition->getCurrentState(), $transition->getInput());
            $this->transitions[$actionKey] = $transition->getNextState();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidInputForState
     */
    public function resolve(State $currentState, Input $input) : State
    {
        $actionKey = $this->getActionKey($currentState, $input);

        if (!empty($this->transitions[$actionKey])) {
            return $this->transitions[$actionKey];
        }

        throw new InvalidInputForState($input, $currentState);
    }

    /**
     * Returns the key to use to store the action involving application of an
     * input to a given state internally.
     *
     * @param State $currentState
     * @param Input $input
     *
     * @return string
     */
    private function getActionKey(State $currentState, Input $input) : string
    {
        return sprintf('%s | %s', $currentState->getName(), $input->getName());
    }
}
