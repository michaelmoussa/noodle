<?php

declare (strict_types = 1);

namespace Noodle\TestAsset;

use Noodle\Stateful\Stateful;
use Noodle\Stateful\StateMaintainer;

class StatefulObject implements Stateful
{
    use StateMaintainer;
}
