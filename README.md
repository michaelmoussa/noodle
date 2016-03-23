# noodle

Noodle is a [finite statemachine](https://en.wikipedia.org/wiki/Finite-state_machine) written in PHP 7

[![Build Status](https://travis-ci.org/michaelmoussa/noodle.svg?branch=master)](https://travis-ci.org/michaelmoussa/noodle)
[![Code Coverage](https://scrutinizer-ci.com/g/michaelmoussa/noodle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/michaelmoussa/noodle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/michaelmoussa/noodle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/michaelmoussa/noodle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/michaelmoussa/noodle/v/stable.png)](https://packagist.org/packages/michaelmoussa/noodle)
[![Latest Unstable Version](https://poser.pugx.org/michaelmoussa/noodle/v/unstable.png)](https://packagist.org/packages/michaelmoussa/noodle)

## Installation

The only officially supported method of installation is [Composer](http://getcomposer.org/)

```bash
composer require michaelmoussa/noodle
```

## Usage

### A simple example

Let's begin with the simple example of a traffic light.

There are three colors - red, yellow, and green. Red lights turn green, green lights turn yellow,
and yellow lights turn red.

Such a system would look something like this:

| Current State | Input        | Next State |
| ------------- | ------------ | ---------- |
| RED           | CHANGE_COLOR | GREEN      |
| GREEN         | CHANGE_COLOR | YELLOW     |
| YELLOW        | CHANGE_COLOR | RED        |

We can build a statemachine to represent that with Noodle as follows:

```php
<?php

declare (strict_types = 1);

require_once 'vendor/autoload.php';

use Noodle\Transition\DefaultTransition;
use Noodle\Transition\Table\DefaultTransitionTable;
use Noodle\State\FlyweightState;
use Noodle\Stateful\Stateful;
use Noodle\Stateful\StateMaintainer;
use Noodle\Statemachine\Statemachine;
use Noodle\Transition\Input\FlyweightInput;

/*
 * Create a Transition table to describe the rules for state transitions. For convenience, the
 * DefaultTransition creates transitions based on a pattern, in this
 * case: <CURRENT_STATE> + <INPUT> = <NEXT_STATE>. If you'd like to use a different pattern,
 * you can pass a regex to DefaultTransition::usePattern(...) to substitute your own. The only
 * requirement is that it is a valid regular expression, and that it uses capture groups with
 * the following names: "current_state", "input", "next_state".
 *
 * Alternatively, you could use the following syntax to define transitions, if you wish:
 *     new DefaultTransition(
 *         FlyweightState::named('RED'),
 *         FlyweightInput::named('CHANGE_COLOR'),
 *         FlyweightState::named('GREEN')
 *     )
 */
$table = new DefaultTransitionTable(
    DefaultTransition::new('RED + CHANGE_COLOR = GREEN'),
    DefaultTransition::new('GREEN + CHANGE_COLOR = YELLOW'),
    DefaultTransition::new('YELLOW + CHANGE_COLOR = RED')
);
$statemachine = new Statemachine($table);

/*
 * Any objects that utilize the statemachine must implement the Stateful interface. For
 * convenience, the StateMaintainer trait is available to satisfy the bare minimum
 * requirements of the interface.
 */
class TrafficLight implements Stateful
{
    use StateMaintainer;
}

// Create the stateful object
$trafficLight = new TrafficLight();

/*
 * Give it a default state. In this case, RED. Noodle makes heavy use of Flyweight objects so
 * as to not have to create totally new instances of various States and Inputs throughout
 * your application.
 */
$trafficLight->setCurrentState(FlyweightState::named('RED'));

// Trigger a state transition on $trafficLIght using the CHANGE_COLOR input
$statemachine->trigger(FlyweightInput::named('CHANGE_COLOR'), $trafficLight);

// The light is now green
echo $trafficLight->getCurrentStateName(); // prints "GREEN"
```

### Events

The above is fairly straightforward, but not terribly interesting. What if we need to do
something special before or after a light changes color? We can implement that logic using events.

There are a total of twelve events that a Noodle statemachine will emit which you can listen for.
They are, in order:

-   **before** a _specific input_ is applied to **a specific state**
-   **before** _any input_ is applied to **any state**
-   **before** a _specific input_ is applied to **any state**
-   **before** _any input_ is applied to a **specific state**
-   **on** _any input_ being applied to _any state_
-   **after** a _specific input_ is applied to **a specific state**
-   **after** _any input_ is applied to **any state**
-   **after** a _specific input_ is applied to **any state**
-   **after** _any input_ is applied to a **specific state**

Suppose that, before every time the light changed color, you wanted to announce it out loud.
Here's one way you could hook that up:

```php
<?php

use League\Event\EventInterface;
use Noodle\Listener\InvokableListener;
use Noodle\State\State;
use Noodle\Stateful\Stateful;
use Noodle\Transition\Input\Input;

class LightChangingAnnouncement extends InvokableListener
{
    public function __invoke(
        EventInterface $event,
        Stateful $object,
        \ArrayObject $context,
        Input $input,
        State $nextState
    ) {
        echo sprintf('Hey everyone, the light is about to turn %s!', $nextState->getName());
    }
}

/*
 * $statemachine is from the previous example. Note the FlyweightState::any() here. This is
 * a "wildcard" state that, for the purposes of the statemachine event system, will match any
 * current state. This is useful in cases where you don't care what the state is, and you
 * know that you want to execute the event every time there's a state change.
 */
$statemachine->before(
    FlyweightInput::named('CHANGE_COLOR'),
    FlyweightState::any(),
    new LightChangingAnnouncement()
);
```

Now, before any light changes color, it will announce the color that it's going to turn to.
You can hook into other events using `->after(...)`, with an optional 4th `int` parameter
representing this event listener's priority relative to other listeners.

Noodle uses the popular [`league/event`](https://github.com/thephpleague/event) library for
its event system, and provides the `InvokableListener` abstract class for convenience, but
you're free to use your own listeners so long as the implement
the `League\Event\ListenerInterface`.

### Failures and State Changes

If any of the listeners triggered prior to the state transition indicates it has failed by
calling the `$event->stopPropagation()` method, Noodle will execute the
`Noodle\Listeners\ReportsTransitionFailures` listener, which throws a `StateTransitionFailed`
exception. This is the default error handling mechanism used by Noodle. If you want to handle
errors differently, you can pass your own listener as the optional 2nd parameter to the
`Statemachine` constructor, and it will be used instead. Naturally, if you throw an exception
in one of your listeners rather than stopping propagation, then Noodle will allow that
exception to propagate out to your application.

State transitions are, by default, handled by the `Noodle\Listener\ChangesState` listener,
which simply calls the `setCurrentState(...)` method of your `Stateful` object. This listener
is triggered by the only `on` event that Noodle emits. You _shouldn't_ need to make any
changes to it that can't be otherwise done in an event listener, but if you absolutely must,
you can pass your own listener as the 3rd parameter to the `Statemachine` constructor, and
Noodle will use that listener instead of the default `ChangesState` listener to update the
object's state. Of course, you can also add to the existing state transition logic by adding
your own listener to `on` at a higher or lower priority, but you may find it easier to simply
use `before` or `after`.

### Context

Noodle automatically creates a "context" object before it starts triggering events, which is
passes throughout the event cycle. This can be used to carry information from one listener
into another. For example, suppose you had three `before` listeners that performed a variety
of operations, and then a final `before` listener that logged all of the results before
executing the state transition. You could add the operation results to the `$context` object
in your listeners, and then read the `$context` in the logging listener to write the data to
your log.

Note that a new context is created at the start of any state transition, and it will cease to
exist at the end of the event cycle, so you must use it in an event listener prior to event
cycle being completed. The context object is a simple `ArrayObject`, which should be flexible
enough for most use cases.
