<?php

declare (strict_types = 1);

namespace Noodle\Transition\Input;

class FlyweightInputTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesNamedInputs()
    {
        $this->assertContains(CreatesWithName::class, class_implements(FlyweightInput::class));

        $name = 'FOO';
        $input = FlyweightInput::named($name);

        $this->assertInstanceOf(Input::class, $input);
        $this->assertSame($name, $input->getName());
    }

    public function testReusesExistingInputs()
    {
        $name = 'FOO';

        $this->assertSame(
            FlyweightInput::named($name),
            FlyweightInput::named($name)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testWildcardAnyInputIsFortyCharHexString()
    {
        $this->assertRegExp(
            '/^[0-9a-f]{40}$/',
            FlyweightInput::any()->getName(),
            'Expected 40 lowercase hexadecimal characters'
        );
    }
}
