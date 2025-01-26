<?php

declare(strict_types=1);

namespace MulAgent\Tool;

use MulAgent\Exceptions\ExceptionFactory;

final class ToolFormatter
{
    /**
     * @param  ToolDefinition  $toolInfo
     * @return array<mixed>
     */
    public static function formatToolDefinitionAsJsonSchema(ToolDefinition $toolInfo): array
    {
        $function = [
            'name' => $toolInfo->name,
        ];
        if (null !== $toolInfo->description) {
            $function['description'] = $toolInfo->description;
        }
        $properties = [];
        foreach ($toolInfo->properties as $property) {
            $properties = array_merge($properties, self::formatProperty($property));
        }
        $function = array_merge($function, [
            'strict' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $toolInfo->required,
                'additionalProperties' => false,
            ]
        ]);
        return [
            'type' => 'function',
            'function' => $function,
        ];
    }

    /**
     * @param  Property  $property
     * @return array<string, mixed>
     */
    public static function formatProperty(Property $property): array
    {
        $param = [
            'type' => $property->type,
        ];
        switch ($property->type) {
            case 'array':
                if (null === $property->items) {
                    throw ExceptionFactory::createToolFormatException('Array type must have items description.');
                }
                $param['items'] = ['type' => $property->items];
                break;
            case 'object':
                if (count($property->properties) === 0) {
                    throw ExceptionFactory::createToolFormatException('Object type must have at least one property description.');
                }
                foreach ($property->properties as $childProperty) {
                    $param['properties'] = array_merge(
                        $param['properties'] ?? [],
                        self::formatProperty($childProperty)
                    );
                }
                break;
        }
        if (count($property->enum) > 0) {
            $param['enum'] = $property->enum;
        }
        if ($property->type === 'string' && null !== $property->format) {
            $param['format'] = $property->format;
        }
        if (null !== $property->description) {
            $param['description'] = $property->description;
        }
        return [
            $property->name => $param,
        ];
    }
}
