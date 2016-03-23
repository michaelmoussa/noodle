<?php

declare (strict_types = 1);

namespace Noodle\Statemachine;

abstract class Exception extends \RuntimeException
{
    /**
     * The default exception message that will be used if none is provided
     *
     * @var
     */
    const MESSAGE = '';

    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous (Optional)
     */
    public function __construct($message = '', int $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            $message ?? static::MESSAGE,
            $code,
            $previous
        );
    }
}
