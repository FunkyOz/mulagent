<?php

declare(strict_types=1);

namespace MulAgent\Tool;

final class ToolCall
{
    /**
     * @param  string  $id
     * @param  string  $name
     * @param  array<mixed>  $arguments
     */
    public function __construct(
        readonly string $id,
        readonly string $name,
        readonly array $arguments,
    ) {
    }
}
