<?php

declare (strict_types = 1);

namespace Noodle\Stateful;

use Noodle\State\Exception\StateNotSet;
use Noodle\State\State;

trait StateMaintainer
{
    /**
     * The object's current state
     *
     * @var State
     */
    private $currentState;

    /**
     * {@inheritdoc}
     */
    public function hasCurrentState() : bool
    {
        return !empty($this->currentState);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentState() : State
    {
        $this->assertHasCurrentState();

        return $this->currentState;
    }

    /**
     * {@inheritdoc}
     *
     */
    public function getCurrentStateName() : string
    {
        $this->assertHasCurrentState();

        return $this->currentState->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentState(State $currentState)
    {
        $this->currentState = $currentState;
    }

    /**
     * Checks if the object has a current state set and throws an exception if not.
     *
     * @throws StateNotSet If the object does not have a current state
     */
    private function assertHasCurrentState()
    {
        if (!$this->hasCurrentState()) {
            throw new StateNotSet(sprintf('%s has no current state', __CLASS__));
        }

    }
}
