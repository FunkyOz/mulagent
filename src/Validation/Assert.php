<?php

declare(strict_types=1);

namespace Mulagent\Validation;

use Assert\Assert as BaseAssert;
use Mulagent\Exceptions\LazyAssertionException;

class Assert extends BaseAssert
{
    protected static $lazyAssertionExceptionClass = LazyAssertionException::class;
    protected static $assertionClass = Assertion::class;
}
