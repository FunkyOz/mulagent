<?php

declare(strict_types=1);

namespace Mulagent\Tool;

class ToolDefinition
{
    /**
     * @param  string  $name
     * @param  string|null  $description
     * @param  array<Property>  $properties
     * @param  array<string>  $required
     */
    public function __construct(
        readonly string $name,
        readonly ?string $description = null,
        readonly array $properties = [],
        readonly array $required = [],
    ) {
    }
}
