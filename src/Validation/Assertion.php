<?php

declare(strict_types=1);

namespace Mulagent\Validation;

use Assert\Assertion as BaseAssertion;
use Mulagent\Exceptions\AssertionException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = AssertionException::class;
}
