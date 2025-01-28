<?php

declare(strict_types=1);

namespace MulAgent\Tool;

use MulAgent\Exceptions\ExceptionFactory;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use stdClass;

final class ToolFormatter
{
    /**
     * @param  callable&object  $tool
     * @return array<string, mixed>
     */
    public static function formatToolAsJsonSchema($tool): array
    {
        $reflectionClass = new ReflectionClass($tool);
        $invokeMethod = self::ensureFunctor($reflectionClass);
        $schema = [
            'strict' => true,
            'name' => self::getName($tool),
        ];
        if (property_exists($tool, 'description')) {
            $schema['description'] = $tool->description;
        }
        $properties = [];
        $required = [];
        foreach ($invokeMethod->getParameters() as $parameter) {
            [$paramSchema, $isRequired] = self::parseParameter($parameter);
            $properties[$paramSchema['name']] = $paramSchema['schema'];

            if ($isRequired) {
                $required[] = $paramSchema['name'];
            }
        }
        $schema['parameters'] = [
            'type' => 'object',
            'properties' => count($properties) > 0 ? $properties : new stdClass(),
            'required' => $required,
            'additionalProperties' => false,
        ];
        return [
            'type' => 'function',
            'function' => $schema,
        ];
    }

    /**
     * @param  ReflectionClass<callable&object>  $reflectionClass
     * @return ReflectionMethod
     */
    private static function ensureFunctor(ReflectionClass $reflectionClass): ReflectionMethod
    {
        if (!$reflectionClass->hasMethod('__invoke')) {
            throw ExceptionFactory::createToolFormatException('Object must have __invoke method');
        }
        return $reflectionClass->getMethod('__invoke');
    }

    /**
     * @param  callable&object  $tool
     * @return non-empty-string
     */
    public static function getName($tool): string
    {
        $reflectionClass = new ReflectionClass($tool);

        self::ensureFunctor($reflectionClass);
        $name = $reflectionClass->getShortName();
        if (property_exists($tool, 'name')) {
            $name = $tool->name;
        } elseif ($reflectionClass->isAnonymous()) {
            throw ExceptionFactory::createToolFormatException('Cannot use anonymous classes as tool without the $name property');
        }
        $name = self::formatJsonSchemaName($name);
        if (empty($name)) {
            throw ExceptionFactory::createToolFormatException('Tool name cannot be empty');
        }
        return $name;
    }

    public static function formatJsonSchemaName(string $name): string
    {
        $separator = '_';
        if (!ctype_lower($name)) {
            $value = (string)preg_replace('/\s+/u', '', ucwords($name));
            $name = mb_strtolower((string)preg_replace('/(.)(?=[A-Z])/u', '$1' . $separator, $value), 'UTF-8');
        }
        $name = (string)str_replace('-', $separator, $name);
        $name = (string)preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($name));
        $name = (string)preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $name);
        return trim($name, $separator);
    }

    /**
     * @param  ReflectionParameter  $parameter
     * @return string|null
     */
    private static function extractParameterDescription(ReflectionParameter $parameter): ?string
    {
        $docComment = $parameter->getDeclaringFunction()->getDocComment();
        if ($docComment === false) {
            return null;
        }

        $docComment = (string)preg_replace('/^\s*\*\s*/m', '', trim($docComment, "/* \t\n\r\0\x0B"));
        $lines = explode("\n", $docComment);
        $paramPattern = '/@param\s+(?:[^\s]+)\s+\$([^\s]+)\s+(.+)/';
        $currentParam = null;
        $description = '';
        foreach ($lines as $line) {
            if (preg_match($paramPattern, $line, $matches)) {
                if ($currentParam === $parameter->getName()) {
                    break;
                }
                $currentParam = $matches[1];
                if ($currentParam === $parameter->getName()) {
                    $description = $matches[2];
                }
                continue;
            }
            if ($currentParam === $parameter->getName() && !str_starts_with(trim($line), '@')) {
                $description .= ' ' . trim($line);
            }
            if ($currentParam === $parameter->getName() && str_starts_with(trim($line), '@')) {
                break;
            }
        }
        return $currentParam === $parameter->getName() ?
            trim((string)preg_replace('/\s+/', ' ', $description)) :
            null;
    }

    /**
     * @param  ReflectionParameter  $parameter
     * @return array{0: array{name: string, schema: array<string, mixed>}, 1: bool}
     */
    private static function parseParameter(ReflectionParameter $parameter): array
    {
        $type = $parameter->getType();
        $schema = self::parseType($type);
        $description = self::extractParameterDescription($parameter);
        if (!empty($description)) {
            $schema['description'] = $description;
        }
        return [
            ['name' => $parameter->getName(), 'schema' => $schema],
            !$parameter->allowsNull()
        ];
    }

    /**
     * @param  ReflectionType|null  $type
     * @return array<string, mixed>
     */
    private static function parseType(?ReflectionType $type): array
    {
        if ($type === null) {
            return ['type' => 'mixed'];
        }

        if ($type instanceof ReflectionUnionType) {
            $types = array_map(
                fn (ReflectionType $t) => self::parseType($t)['type'],
                $type->getTypes()
            );
            return ['type' => array_values(array_unique($types))];
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
            if ($type->isBuiltin()) {
                return ['type' => self::phpTypeToJsonType($typeName)];
            }
            if ($typeName === 'array') {
                return [
                    'type' => 'array',
                    'items' => ['type' => 'mixed']
                ];
            }
            if (enum_exists($typeName)) {
                $reflectionEnum = new ReflectionEnum($typeName);
                $type = 'string';
                if ($reflectionEnum->isBacked()) {
                    $backingType = $reflectionEnum->getBackingType();
                    if ($backingType instanceof ReflectionNamedType) {
                        $type = self::phpTypeToJsonType($backingType->getName());
                    }
                    $cases = array_map(
                        fn (ReflectionEnumBackedCase $case) => $case->getBackingValue(),
                        $reflectionEnum->getCases()
                    );
                } else {
                    $cases = array_map(
                        fn (ReflectionEnumUnitCase $case) => $case->getName(),
                        $reflectionEnum->getCases()
                    );
                }
                return [
                    'type' => $type,
                    'enum' => $cases
                ];
            }
            if (class_exists($typeName)) {
                $reflectionClass = new ReflectionClass($typeName);
                $properties = [];
                $required = [];
                foreach ($reflectionClass->getProperties() as $property) {
                    $propertyType = $property->getType();
                    $propertySchema = self::parseType($propertyType);
                    $properties[$property->getName()] = $propertySchema;
                    if (!$propertyType?->allowsNull()) {
                        $required[] = $property->getName();
                    }
                }
                $schema = ['type' => 'object'];
                if (!empty($properties)) {
                    $schema['properties'] = $properties;
                    if (!empty($required)) {
                        $schema['required'] = $required;
                    }
                }
                return $schema;
            }
        }
        return ['type' => 'mixed'];
    }

    private static function phpTypeToJsonType(string $phpType): string
    {
        return match ($phpType) {
            'float' => 'number',
            'int' => 'integer',
            'bool' => 'boolean',
            'array' => 'array',
            'object', 'mixed' => 'object',
            default => 'string'
        };
    }
}
