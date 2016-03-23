<?php

declare (strict_types = 1);

namespace Noodle\Transition;

use Noodle\State\FlyweightState;
use Noodle\Transition\Exception\InvalidPattern;
use Noodle\Transition\Exception\TransitionPatternMismatch;
use Noodle\Transition\Input\FlyweightInput;

class DefaultTransitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $defaultPattern;

    public static function setUpBeforeClass()
    {
        self::$defaultPattern = DefaultTransition::getPattern();
    }

    public static function tearDownAfterClass()
    {
        DefaultTransition::usePattern(self::$defaultPattern);
    }

    public function testCanCreateTransitionsUsingConstructor()
    {
        $currentState = FlyweightState::named('FOO');
        $input = FlyweightInput::named('BAR');
        $nextState = FlyweightState::named('BAZ');

        $transition = new DefaultTransition($currentState, $input, $nextState);

        $this->assertInstanceOf(Transition::class, $transition);
        $this->assertSame($currentState, $transition->getCurrentState());
        $this->assertSame($input, $transition->getInput());
        $this->assertSame($nextState, $transition->getNextState());
    }

    public function testCanCreateTransitionsFromDefaultPattern()
    {
        $this->assertContains(CreateableFromPattern::class, class_implements(DefaultTransition::class));

        $currentState = 'FOO';
        $input = 'BAR';
        $nextState = 'BAZ';

        $transition = DefaultTransition::new(sprintf('%s + %s = %s', $currentState, $input, $nextState));

        $this->assertInstanceOf(Transition::class, $transition);
        $this->assertSame($currentState, $transition->getCurrentState()->getName());
        $this->assertSame($input, $transition->getInput()->getName());
        $this->assertSame($nextState, $transition->getNextState()->getName());
    }

    public function testThrowsExceptionIfTransitionStringDoesNotMatchPattern()
    {
        $transitionString = 'kaboom!';

        $this->expectException(TransitionPatternMismatch::class);
        $this->expectExceptionMessage(
            sprintf(
                'The provided transition string "%s" does not match the configured pattern: "%s"',
                $transitionString,
                DefaultTransition::getPattern()
            )
        );

        DefaultTransition::new($transitionString);
    }

    public function testCanSetValidPattern()
    {
        $pattern = '/foo/';

        DefaultTransition::usePattern($pattern);

        $this->assertSame($pattern, DefaultTransition::getPattern());
    }

    public function testThrowsExceptionIfSettingInvalidPattern()
    {
        $invalidPattern = 'foo';

        $this->expectException(InvalidPattern::class);
        $this->expectExceptionMessage(
            sprintf(
                'The supplied pattern "%s" does not appear to be a valid regular expression',
                $invalidPattern
            )
        );

        DefaultTransition::usePattern($invalidPattern);
    }
}
