<?php

declare(strict_types=1);

namespace MulAgent\Tool;

class Property
{
    /**
     * @param  string  $type
     * @param  string  $name
     * @param  string|null  $description
     * @param  array<int|string>  $enum
     * @param  string|null  $items
     * @param  array<Property>  $properties
     * @param  string|null  $format
     */
    public function __construct(
        readonly string $type,
        readonly string $name,
        readonly ?string $description = null,
        readonly array $enum = [],
        readonly ?string $items = null,
        readonly array $properties = [],
        readonly ?string $format = null,
    ) {
    }
}
