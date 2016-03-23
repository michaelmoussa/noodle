<?php

namespace Noodle\Transition;

use Noodle\State\FlyweightState;
use Noodle\Transition\Exception\InvalidPattern;
use Noodle\Transition\Exception\TransitionPatternMismatch;
use Noodle\Transition\Input\FlyweightInput;

trait CreatesFromPattern
{
    /**
     * The regex pattern to use for creating Transitions
     *
     * @var string
     */
    private static $pattern = "/^(?P<current_state>[^+]+) \+ (?P<input>[^=]+) = (?P<next_state>.+)$/";

    /**
     * {@inheritdoc}
     *
     * @throws TransitionPatternMismatch
     */
    public static function new(string $transition) : Transition
    {
        $isMatch = preg_match(self::getPattern(), $transition, $matches);
        $withExpectedNamedCaptures = isset($matches['current_state'], $matches['input'], $matches['next_state']);

        if (!$isMatch || !$withExpectedNamedCaptures) {
            throw new TransitionPatternMismatch($transition, self::$pattern);
        }

        return new self(
            FlyweightState::named($matches['current_state']),
            FlyweightInput::named($matches['input']),
            FlyweightState::named($matches['next_state'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getPattern() : string
    {
        return self::$pattern;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidPattern
     */
    public static function usePattern(string $pattern)
    {
        if (@preg_match($pattern, null) === false) {
            throw new InvalidPattern($pattern);
        }

        self::$pattern = $pattern;
    }
}
