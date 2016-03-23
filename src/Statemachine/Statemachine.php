<?php

namespace Noodle\Statemachine;

use ArrayObject as Context;
use Generator;
use League\Event\Emitter;
use League\Event\Event;
use League\Event\ListenerInterface as Listener;
use Noodle\Listener\ChangesState;
use Noodle\Listener\ReportsTransitionFailures;
use Noodle\State\FlyweightState;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Statemachine\Exception as NoodleException;
use Noodle\Statemachine\Exception\StateTransitionFailed;
use Noodle\Transition\Input\FlyweightInput;
use Noodle\Transition\Input\Input;
use Noodle\Transition\Table\TransitionTable;

final class Statemachine implements EventfulStatemachine
{
    /**
     * The emitter used to emit events
     *
     * @var Emitter
     */
    private $emitter;

    /**
     * Transition table used to determine which states support which actions
     *
     * @var TransitionTable
     */
    private $stateTransitionTable;

    /**
     * Constructor
     *
     * @param TransitionTable $stateTransitionTable
     * @param Listener $failureHandler (Optional) Listener that handles transition failures
     * @param Listener $stateChanger (Optional) Listener that updates the object's state
     */
    public function __construct(
        TransitionTable $stateTransitionTable,
        Listener $failureHandler = null,
        Listener $stateChanger = null
    ) {
        $this->stateTransitionTable = $stateTransitionTable;
        $this->emitter = new Emitter();

        if (!$stateChanger) {
            $stateChanger = new ChangesState();
        }

        $this->emitter->addListener(
            $this->getEventName('on', FlyweightInput::any(), FlyweightState::any()),
            $stateChanger
        );

        if (!$failureHandler) {
            $failureHandler = new ReportsTransitionFailures();
        }

        $this->emitter->addListener('failed', $failureHandler);
    }

    /**
     * {@inheritdoc}
     */
    public function trigger(Input $input, Stateful $object)
    {
        $context = new Context();
        $nextState = $this->stateTransitionTable->resolve($object->getCurrentState(), $input);

        $this->emitEvents($input, $object, $context, $nextState);
    }

    /**
     * {@inheritdoc}
     */
    public function after(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL)
    {
        $this->emitter->addListener(
            $this->getEventName('after', $input, $currentState),
            $listener,
            $priority
        );
    }

    /**
     * {@inheritdoc}
     */
    public function before(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL)
    {
        $this->emitter->addListener(
            $this->getEventName('before', $input, $currentState),
            $listener,
            $priority
        );
    }

    /**
     * {@inheritdoc}
     */
    public function on(Input $input, State $currentState, Listener $listener, int $priority = Emitter::P_NORMAL)
    {
        $this->emitter->addListener(
            $this->getEventName('on', $input, $currentState),
            $listener,
            $priority
        );
    }

    /**
     * Creates an event name based on an input, current state, and when that event should be emitted
     *
     * @param string $executedWhen
     * @param Input $input
     * @param State $currentState
     *
     * @return string
     */
    private function getEventName(string $executedWhen, Input $input, State $currentState) : string
    {
        return sprintf('%s %s %s', $executedWhen, $input->getName(), $currentState->getName());
    }

    /**
     * Emits the events corresponding to applying the provided input on the provided object.
     *
     * @param Input $input
     * @param Stateful $object
     * @param Context $context
     * @param State $nextState
     *
     * @return void
     *
     * @throws StateTransitionFailed
     */
    private function emitEvents(Input $input, Stateful $object, Context $context, State $nextState)
    {
        /** @var Event $event */
        foreach ($this->eventProvider($input, $object->getCurrentState()) as $event) {
            if (!$this->emitter->hasListeners($event->getName())) {
                continue;
            }

            if ($this->emitter->emit($event, $object, $context, $input, $nextState)->isPropagationStopped()) {
                $this->emitter->emit(Event::named('failed'), $object, $context, $input, $nextState);

                throw new StateTransitionFailed($input, $object, $context, $nextState);
            }
        }
    }

    /**
     * Returns events to be emitted whenever a state transition is attempted
     *
     * @param Input $input
     * @param State $currentState
     *
     * @return Generator
     */
    private function eventProvider(Input $input, State $currentState) : Generator
    {
        $anyInput = FlyweightInput::any();
        $anyState = FlyweightState::any();

        yield Event::named($this->getEventName('before', $input, $currentState));
        yield Event::named($this->getEventName('before', $anyInput, $currentState));
        yield Event::named($this->getEventName('before', $input, $anyState));
        yield Event::named($this->getEventName('before', $anyInput, $anyState));

        yield Event::named($this->getEventName('on', $anyInput, $anyState));

        yield Event::named($this->getEventName('after', $input, $currentState));
        yield Event::named($this->getEventName('after', $anyInput, $currentState));
        yield Event::named($this->getEventName('after', $input, $anyState));
        yield Event::named($this->getEventName('after', $anyInput, $anyState));
    }
}
