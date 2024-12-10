<?php

declare(strict_types=1);

namespace MulAgent\Validation;

use Assert\Assert as BaseAssert;
use MulAgent\Exceptions\LazyAssertionException;

class Assert extends BaseAssert
{
    protected static $lazyAssertionExceptionClass = LazyAssertionException::class;
    protected static $assertionClass = Assertion::class;
}
