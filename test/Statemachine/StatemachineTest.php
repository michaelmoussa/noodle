<?php

declare (strict_types = 1);

namespace Noodle\Statemachine;

use ArrayObject as Context;
use League\Event\CallbackListener;
use League\Event\Event;
use League\Event\EventInterface;
use Noodle\Listener\InvokableListener;
use Noodle\State\FlyweightState;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Statemachine\Exception\StateTransitionFailed;
use Noodle\TestAsset\ChessMatchTransitionTable;
use Noodle\TestAsset\StatefulObject;
use Noodle\Transition\Input\FlyweightInput;
use Noodle\Transition\Input\Input;
use Noodle\Transition\Table\Exception\InvalidInputForState;

class StatemachineTest extends \PHPUnit_Framework_TestCase
{
    public function whenProvider() : \Generator
    {
        foreach (['before', 'on', 'after'] as $when) {
            $eventNames = [];

            if ($when !== 'on') {
                $eventNames = [
                    sprintf(
                        '%s %s %s',
                        $when,
                        FlyweightInput::named('WHITE_MOVES')->getName(),
                        FlyweightState::named('WHITES_TURN')->getName()
                    ),
                    sprintf(
                        '%s %s %s',
                        $when,
                        FlyweightInput::any()->getName(),
                        FlyweightState::named('WHITES_TURN')->getName()
                    ),
                    sprintf(
                        '%s %s %s',
                        $when,
                        FlyweightInput::named('WHITE_MOVES')->getName(),
                        FlyweightState::any()->getName()
                    ),
                ];
            }

            $eventNames[] =  sprintf(
                '%s %s %s',
                $when,
                FlyweightInput::any()->getName(),
                FlyweightState::any()->getName()
            );

            yield [$when, $eventNames];
        }
    }

    /**
     * @dataProvider whenProvider
     *
     * @param string $when
     * @param array $expectedEventNames
     */
    public function testEventsAreEmittedDuringTransitions(string $when, array $expectedEventNames)
    {
        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));
        $statemachine = new Statemachine(
            new ChessMatchTransitionTable()
        );

        $eventRecorder = $this->addRecordingEvents($statemachine, $when);
        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object);

        $this->assertSame(count($expectedEventNames), count($eventRecorder));
        foreach ($expectedEventNames as $index => $eventName) {
            $this->assertSame(
                [
                    $eventName,
                    $object,
                    $eventRecorder->context,
                    FlyweightInput::named('WHITE_MOVES'),
                    FlyweightState::named('BLACKS_TURN'),
                ],
                $eventRecorder[$index]
            );
        }
    }

    public function testChangesObjectStateOnValidTransition()
    {
        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $statemachine = new Statemachine(new ChessMatchTransitionTable());
        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object);

        $this->assertSame(FlyweightState::named('BLACKS_TURN'), $object->getCurrentState());
    }

    public function testTriggerAcceptsOptionalContextParameter()
    {
        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $context = new Context(['foo' => 'bar']);

        $statemachine = new Statemachine(new ChessMatchTransitionTable());
        $eventRecorder = $this->addRecordingEvents($statemachine, 'before');
        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object, $context);

        $this->assertSame($context, $eventRecorder->context);
    }

    public function testReportsFailureIfTransitionFailsDueToInvalidTransition()
    {
        $this->expectException(InvalidInputForState::class);
        $this->expectExceptionMessage('Cannot INVALID MOVE a WHITES_TURN object');

        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $statemachine = new Statemachine(new ChessMatchTransitionTable());
        $statemachine->trigger(FlyweightInput::named('INVALID MOVE'), $object);
    }

    public function testReportsFailureIfTransitionFailsDueToPriorListenerStoppingPropagation()
    {
        $this->expectException(StateTransitionFailed::class);
        $this->expectExceptionMessage(
            'Failed attempting to WHITE_MOVES a Noodle\TestAsset\StatefulObject with current state WHITES_TURN'
        );

        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $statemachine = new Statemachine(new ChessMatchTransitionTable());
        $statemachine->before(
            FlyweightInput::named('WHITE_MOVES'),
            FlyweightState::named('WHITES_TURN'),
            CallbackListener::fromCallable(function (Event $event) {
                $event->stopPropagation();
            })
        );
        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object);
    }

    public function testExceptionsInListenersPropagateOutToApplication()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('kaboom!');

        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $originalException = new \Exception('kaboom!');
        $statemachine = new Statemachine(new ChessMatchTransitionTable());
        $statemachine->before(
            FlyweightInput::named('WHITE_MOVES'),
            FlyweightState::named('WHITES_TURN'),
            CallbackListener::fromCallable(function (Event $event) use ($originalException) {
                throw $originalException;
            })
        );

        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object);
    }

    public function testCanOverrideDefaultStateChangerListener()
    {
        $object = new StatefulObject();
        $object->setCurrentState(FlyweightState::named('WHITES_TURN'));

        $stateChanger = new class extends InvokableListener
        {
            public function __invoke(
                EventInterface $event,
                Stateful $object,
                Context $context,
                Input $input,
                State $nextState
            ) {
                $object->setCurrentState(FlyweightState::named('it worked!'));
            }
        };
        $statemachine = new Statemachine(new ChessMatchTransitionTable(), null, $stateChanger);
        $statemachine->trigger(FlyweightInput::named('WHITE_MOVES'), $object);

        $this->assertSame(FlyweightState::named('it worked!'), $object->getCurrentState());
    }

    private function addRecordingEvents(Statemachine $statemachine, string $when) : \ArrayObject
    {
        $eventRecorder = new class extends \ArrayObject
        {
            public $context = null;

            public function __invoke(
                Event $event,
                Stateful $object,
                Context $context,
                Input $input,
                State $nextState
            ) {
                /*
                 * We only care that the same context was passed to each event, so we'll
                 * keep track of it here for asserting in our tests.
                 */
                if (!$this->context) {
                    $this->context = $context;
                }

                $this->append([$event->getName(), $object, $context, $input, $nextState]);
            }
        };

        if ($when !== 'on') {
            $statemachine->{$when}(
                FlyweightInput::named('WHITE_MOVES'),
                FlyweightState::named('WHITES_TURN'),
                CallbackListener::fromCallable($eventRecorder)
            );
            $statemachine->{$when}(
                FlyweightInput::any(),
                FlyweightState::named('WHITES_TURN'),
                CallbackListener::fromCallable($eventRecorder)
            );
            $statemachine->{$when}(
                FlyweightInput::named('WHITE_MOVES'),
                FlyweightState::any(),
                CallbackListener::fromCallable($eventRecorder)
            );
        }

        $statemachine->{$when}(
            FlyweightInput::any(),
            FlyweightState::any(),
            CallbackListener::fromCallable($eventRecorder)
        );

        return $eventRecorder;
    }
}
