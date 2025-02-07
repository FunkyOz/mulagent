<?php

use Anthropic\Responses\Messages\CreateResponse;
use MulAgent\LLM\Anthropic\AnthropicLLM;
use MulAgent\Message\Message;
use MulAgent\Message\MessageRole;

it('should respond with simple text', function () {
    $config = createFakeAnthropicConfig([
        createFakeAnthropicChatResponse([
            'content' => 'This is a test'
        ]),
    ]);
    $llm = new AnthropicLLM($config);
    $result = $llm->chat();
    expect($result->message)
        ->content->{0}->text->toBe('This is a test')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($result->toolCalls)->toBeEmpty();
});

it('should respond with a tool call', function () {
    $config = createFakeAnthropicConfig([
        createFakeAnthropicChatResponse([
            'content' => 'My test!',
            'tool_id' => '1',
            'tool_name' => 'test',
            'tool_args' => ['a' => 1, 'b' => 2, 'c' => 3],
        ]),
    ]);
    $llm = new AnthropicLLM($config);
    $result = $llm->chat();
    expect($result->message)
        ->content->{0}->text->toBe('My test!')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($result->toolCalls[0])
        ->id->toBe('1')
        ->name->toBe('test')
        ->arguments->toMatchArray([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);
});

it('should respond with more tool calls', function () {
    $config = createFakeAnthropicConfig([
        CreateResponse::fake([
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'This is a test'
                ],
                [
                    'type' => 'tool_use',
                    'id' => 'tool_1',
                    'name' => 'tool_1',
                    'input' => ['arg1' => 1, 'arg2' => 2],
                ],
                [
                    'type' => 'tool_use',
                    'id' => 'tool_2',
                    'name' => 'tool_2',
                    'input' => ['param1' => 1, 'param2' => 2],
                ]
            ]
        ]),
    ]);
    $llm = new AnthropicLLM($config);
    $result = $llm->chat();
    expect($result->message)
        ->content->{0}->text->toBe('This is a test')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($result->toolCalls[0])
        ->id->toBe('tool_1')
        ->name->toBe('tool_1')
        ->arguments->toMatchArray([
            'arg1' => 1,
            'arg2' => 2,
        ])
        ->and($result->toolCalls[1])
        ->id->toBe('tool_2')
        ->name->toBe('tool_2')
        ->arguments->toMatchArray([
            'param1' => 1,
            'param2' => 2,
        ]);
});

it('should should request correctly parse', function () {
    $config = createFakeAnthropicConfig([
        createFakeAnthropicChatResponse([
            'content' => 'test',
        ]),
    ]);
    $messages = [
        Message::system('System message'),
        Message::user('User message'),
        Message::assistant('Assistant message', [
            'tool_calls' => [
                [
                    'type' => 'tool_use',
                    'id' => 'tool_1',
                    'name' => 'tool_1',
                    'input' => ['arg1' => 1, 'arg2' => 2],
                ]
            ]
        ]),
        Message::tool('Tool output', [
            'tool_call_id' => 'tool_1'
        ])
    ];
    $tools = [
        new class () {
            public string $name = 'test_tool';

            public function __invoke(string $param1): string
            {
                return '';
            }
        }
    ];
    $llm = new AnthropicLLM($config);
    $llm->chat($messages, $tools);
    $config->client->messages()->assertSent(function (string $method, array $parameters) {
        if ('create' !== $method) {
            return false;
        }
        expect($parameters)
            ->toMatchArray([
                'model' => 'claude-3-5-haiku-20241022',
                'max_tokens' => 1024,
                'system' => 'System message',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'User message'
                            ]
                        ]
                    ],
                    [
                        'role' => 'assistant',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Assistant message'
                            ],
                            [
                                'type' => 'tool_use',
                                'id' => 'tool_1',
                                'name' => 'tool_1',
                                'input' => ['arg1' => 1, 'arg2' => 2],
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'tool_result',
                                'tool_use_id' => 'tool_1',
                                'content' => 'Tool output'
                            ]
                        ]
                    ],
                ],
                'tools' => [
                    [
                        'name' => 'test_tool',
                        'input_schema' => [
                            'type' => 'object',
                            'properties' => [
                                'param1' => [
                                    'type' => 'string',
                                ]
                            ],
                            'required' => [
                                'param1',
                            ],
                            'additionalProperties' => false
                        ]
                    ]
                ]
            ]);
        return true;
    });
});
