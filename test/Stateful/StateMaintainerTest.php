<?php

declare (strict_types = 1);

namespace Noodle\Stateful;

use Noodle\State\Exception\StateNotSet;
use Noodle\State\FlyweightState;

class StateMaintainerTest extends \PHPUnit_Framework_TestCase
{
    use StateMaintainer;

    public function testThrowsUnexpectedValueExceptionIfGettingUnsetState()
    {
        $this->expectException(StateNotSet::class);
        $this->expectExceptionMessage('Noodle\Stateful\StateMaintainerTest has no current state');

        $object = new self();
        $object->getCurrentState();
    }

    public function testThrowsUnexpectedValueExceptionIfGettingUnsetStateName()
    {
        $this->expectException(StateNotSet::class);
        $this->expectExceptionMessage('Noodle\Stateful\StateMaintainerTest has no current state');

        $object = new self();
        $object->getCurrentStateName();
    }

    public function testCanCheckIfCurrentStateIsSet()
    {
        $this->currentState = FlyweightState::named('FOO');

        $this->assertTrue($this->hasCurrentState());
    }

    public function testCanCheckIfCurrentStateIsUnset()
    {
        $this->assertFalse($this->hasCurrentState());
    }

    public function testCanSetCurrentState()
    {
        $state = FlyweightState::named('FOO');
        $this->setCurrentState($state);

        $this->assertSame($this->currentState, $state);
    }

    public function testCanRetrieveCurrentState()
    {
        $this->setCurrentState(FlyweightState::named('FOO'));

        $this->assertSame($this->currentState, $this->getCurrentState());
    }

    public function testCanRetrieveCurrentStateName()
    {
        $stateName = 'FOO';
        $this->setCurrentState(FlyweightState::named($stateName));

        $this->assertSame($stateName, $this->getCurrentStateName());
    }
}
