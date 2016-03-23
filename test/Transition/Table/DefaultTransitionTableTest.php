<?php

declare (strict_types = 1);

namespace Noodle\Transition\Table;

use Noodle\State\FlyweightState;
use Noodle\TestAsset\ChessMatchTransitionTable;
use Noodle\Transition\DefaultTransition;
use Noodle\Transition\Input\FlyweightInput;
use Noodle\Transition\Table\Exception\InvalidInputForState;

class DefaultTransitionTableTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionWhenProvidingInvalidInputToState()
    {
        $this->expectException(InvalidInputForState::class);
        $this->expectExceptionMessage('Cannot CLOSE a CLOSED object');

        $transitionTable = new DefaultTransitionTable(
            DefaultTransition::new('CLOSED + OPEN = OPENED')
        );

        try {
            $transitionTable->resolve(FlyweightState::named('CLOSED'), FlyweightInput::named('CLOSE'));
        } catch (InvalidInputForState $exception) {
            $this->assertSame(FlyweightInput::named('CLOSE'), $exception->getInput());
            $this->assertSame(FlyweightState::named('CLOSED'), $exception->getState());

            throw $exception;
        }
    }

    public function movesProvider()
    {
        return [
            ['WHITES_TURN', ['WHITE_MOVES', 'BLACK_MOVES'], 'WHITES_TURN'],
            ['WHITES_TURN', ['WHITE_MOVES'], 'BLACKS_TURN'],
            ['WHITES_TURN', ['WHITE_MOVES', 'BLACK_MOVES', 'WHITE_MOVES', 'BLACK_MOVES', 'WHITE_MOVES'], 'BLACKS_TURN'],
            ['BLACKS_TURN', ['BLACK_MOVES', 'WHITE_MOVES', 'CHECKMATE'], 'BLACK_WINS'],
            ['BLACKS_TURN', ['BLACK_MOVES', 'WHITE_MOVES', 'BLACK_MOVES', 'STALEMATE'], 'DRAW'],
        ];
    }

    /**
     * @dataProvider movesProvider
     *
     * @param string $currentStateName
     * @param array $inputNames
     * @param string $expectedStateName
     */
    public function testResolvesStateTransitionsProperly(
        string $currentStateName,
        array $inputNames,
        string $expectedStateName
    ) {
        $table = new ChessMatchTransitionTable();

        $currentState = FlyweightState::named($currentStateName);
        $expectedState = FlyweightState::named($expectedStateName);

        foreach ($inputNames as $inputName) {
            $currentState = $table->resolve($currentState, FlyweightInput::named($inputName));
        }

        $this->assertSame(
            $expectedState,
            $currentState
        );
    }
}
