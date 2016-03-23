<?php

declare (strict_types = 1);

namespace Noodle\State;

/**
 * FlyweightState will reuse states it has previously created with the same name
 * rather than create new ones. This results in some memory and performance savings
 * in applications that would otherwise have many repeated "new State('...')", and
 * it allows for easier comparison of two States, if necessary.
 *
 * @see https://en.wikipedia.org/wiki/Flyweight_pattern
 */
final class FlyweightState implements CreatesWildcard, CreatesWithName, State
{
    /**
     * The name of the state
     *
     * @var string
     */
    private $name;

    /**
     * Instances of states created by the FlyweightState factory method
     *
     * @var State[]
     */
    private static $instances = [];

    /**
     * The "wildcard" state, indicating "any" state when doing transition events.
     *
     * @var State
     */
    private static $wildcard;

    /**
     * Constructor
     *
     * @param string $name
     */
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Creates and returns a state with the given name, or, if such a state
     * has already been created, returns it from the instance cache.
     *
     * @param string $name
     *
     * @return State
     */
    public static function named(string $name) : State
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * Creates and returns a state with a long, random, name in hex to be
     * designated the "any" / "wildcard" state, or, if such a state has
     * already been created, returns it.
     *
     * @return State
     */
    public static function any() : State
    {
        if (!isset(self::$wildcard)) {
            self::$wildcard = new self(bin2hex(random_bytes(20)));
        }

        return self::$wildcard;
    }
}
