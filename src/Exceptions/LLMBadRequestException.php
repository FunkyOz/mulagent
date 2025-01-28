<?php

declare(strict_types=1);

namespace MulAgent\Exceptions;

use Throwable;

class LLMBadRequestException extends MulagentException
{
    /**
     * @param  array<mixed>  $parameters
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        private readonly array $parameters = [],
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
