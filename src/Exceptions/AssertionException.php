<?php

declare(strict_types=1);

namespace MulAgent\Exceptions;

use Assert\AssertionFailedException;
use Stringable;
use Throwable;

class AssertionException extends MulagentException implements Stringable, AssertionFailedException
{
    /**
     * @param  string  $message
     * @param  int  $code
     * @param  string|null  $propertyPath
     * @param  mixed  $value
     * @param  array<mixed>  $constraints
     * @param  Throwable|null  $previous
     */
    public function __construct(
        string $message,
        int $code,
        private ?string $propertyPath = null,
        private mixed $value = null,
        private array $constraints = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
