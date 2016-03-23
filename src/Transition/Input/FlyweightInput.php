<?php

declare (strict_types = 1);

namespace Noodle\Transition\Input;

/**
 * FlyweightInput will reuse inputs it has previously created with the same name
 * rather than create new ones. This results in some memory and performance savings
 * in applications that would otherwise have many repeated "new Input('...')", and
 * it allows for easier comparison of two Inputs, if necessary.
 *
 * @see https://en.wikipedia.org/wiki/Flyweight_pattern
 */
final class FlyweightInput implements CreatesWildcard, CreatesWithName, Input
{
    /**
     * The name of the input
     *
     * @var string
     */
    private $name;

    /**
     * Instances of inputs created by the FlyweightInput factory method
     *
     * @var Input[]
     */
    private static $instances = [];

    /**
     * The "wildcard" input, indicating "any" input when doing transition events.
     *
     * @var Input
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
     * Creates and returns an input with the given name, or, if such an input
     * has already been created, returns it from the instance cache.
     *
     * @param string $name
     *
     * @return Input
     */
    public static function named(string $name) : Input
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * Creates and returns an input with a long, random, name in hex to be
     * designated the "any" / "wildcard" state, or, if such an input has
     * already been created, returns it.
     *
     * @return Input
     */
    public static function any() : Input
    {
        if (!isset(self::$wildcard)) {
            self::$wildcard = new self(bin2hex(random_bytes(20)));
        }

        return self::$wildcard;
    }
}
