<?php

declare (strict_types = 1);

namespace Noodle\TestAsset;

use Noodle\State\State;
use Noodle\Transition\DefaultTransition;
use Noodle\Transition\Input\Input;
use Noodle\Transition\Table\DefaultTransitionTable;
use Noodle\Transition\Table\TransitionTable;

class ChessMatchTransitionTable implements TransitionTable
{
    /**
     * @var TransitionTable
     */
    private $wrappedTransitionTable;

    public function __construct()
    {
        $this->wrappedTransitionTable = new DefaultTransitionTable(
            DefaultTransition::new('WHITES_TURN + WHITE_MOVES = BLACKS_TURN'),
            DefaultTransition::new('BLACKS_TURN + BLACK_MOVES = WHITES_TURN'),
            DefaultTransition::new('WHITES_TURN + CHECKMATE = WHITE_WINS'),
            DefaultTransition::new('BLACKS_TURN + CHECKMATE = BLACK_WINS'),
            DefaultTransition::new('WHITES_TURN + STALEMATE = DRAW'),
            DefaultTransition::new('BLACKS_TURN + STALEMATE = DRAW')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(State $currentState, Input $input) : State
    {
        return $this->wrappedTransitionTable->resolve($currentState, $input);
    }
}
