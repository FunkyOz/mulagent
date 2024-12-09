<?php

use Mulagent\Tool\Property;
use Mulagent\Tool\ToolFormatter;
use Mulagent\Tool\ToolDefinition;

it('should format property', function (Property $property, array $expected) {
    expect(ToolFormatter::formatProperty($property))
        ->toMatchArray($expected);
})->with([
    [
        new Property(
            'object',
            'my_property',
            'This is my property',
            [],
            null,
            [new Property('number', 'foo_property')]
        ),
        [
            'my_property' => [
                'type' => 'object',
                'description' => 'This is my property',
                'properties' => ['foo_property' => ['type' => 'number']],
            ],
        ],
    ],
    [
        new Property(
            'array',
            'my_property',
            null,
            [],
            'int',
        ),
        [
            'my_property' => [
                'type' => 'array',
                'items' => ['type' => 'int']
            ],
        ],
    ],
    [
        new Property(
            'string',
            'my_property',
        ),
        [
            'my_property' => [
                'type' => 'string',
            ],
        ],
    ],
    [
        new Property(
            'string',
            'my_property',
            null,
            [],
            null,
            [],
            'date-time'
        ),
        [
            'my_property' => [
                'type' => 'string',
                'format' => 'date-time'
            ],
        ],
    ],
]);

it('should format tool as json schema', function () {
    $toolInfo = new ToolDefinition(
        'my_tool',
        'Description of my tool',
        [
            new Property(
                'string',
                'parameter_1',
            ),
            new Property(
                'array',
                'parameter_2',
                'This is my second parameter',
                [],
                'int',
            ),
            new Property(
                'object',
                'parameter_3',
                'This is my third parameter',
                [],
                null,
                [
                    new Property(
                        'number',
                        'parameter_3_1',
                    ),
                    new Property(
                        'string',
                        'parameter_3_2',
                        null,
                        [],
                        null,
                        [],
                        'hostname'
                    ),
                ]
            ),
            new Property(
                'string',
                'parameter_4',
                'This is my fourth parameter',
                ['one', 'two', 'three'],
            ),
        ],
        ['parameter_1', 'parameter_4']
    );

    $expected = [
        'type' => 'function',
        'function' => [
            'name' => 'my_tool',
            'description' => 'Description of my tool',
            'strict' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'parameter_1' => [
                        'type' => 'string',
                    ],
                    'parameter_2' => [
                        'type' => 'array',
                        'description' => 'This is my second parameter',
                        'items' => [
                            'type' => 'int',
                        ],
                    ],
                    'parameter_3' => [
                        'type' => 'object',
                        'description' => 'This is my third parameter',
                        'properties' => [
                            'parameter_3_1' => ['type' => 'number'],
                            'parameter_3_2' => ['type' => 'string', 'format' => 'hostname'],
                        ],
                    ],
                    'parameter_4' => [
                        'type' => 'string',
                        'description' => 'This is my fourth parameter',
                        'enum' => ['one', 'two', 'three'],
                    ],
                ],
                'required' => ['parameter_1', 'parameter_4'],
                'additionalProperties' => false,
            ],
        ],
    ];

    expect(ToolFormatter::formatToolDefinitionAsJsonSchema($toolInfo))
        ->toMatchArray($expected);
});
