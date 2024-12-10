<?php

declare(strict_types=1);

namespace MulAgent\Validation;

use Assert\Assertion as BaseAssertion;
use MulAgent\Exceptions\AssertionException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = AssertionException::class;
}
