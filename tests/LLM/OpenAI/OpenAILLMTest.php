<?php

use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\Message\MessageRole;
use MulAgent\Message\TextContent;
use MulAgent\Tool\ToolCall;

it('should create openai llm', function () {
    $config = createFakeOpenAIConfig([
        createFakeOpenAIChatResponse([
            'content' => 'My test!',
        ]),
    ]);
    $llm = new OpenAILLM($config);
    $result = $llm->chat();
    expect($result->message)
        ->content->{0}->text->toBe('My test!')
        ->role->toBe(MessageRole::ASSISTANT);
});

it('should expect text content', function () {
    $config = createFakeOpenAIConfig([
        createFakeOpenAIChatResponse([
            'content' => 'Test response',
        ]),
    ]);
    $llm = new OpenAILLM($config);
    $result = $llm->chat([
        new Message(
            content: new TextContent('Test response'),
            role: MessageRole::USER
        )
    ]);
    expect($result->message)
        ->content->{0}->text->toBe('Test response')
        ->role->toBe(MessageRole::ASSISTANT);
});

it('should respond with tool call', function () {
    $config = createFakeOpenAIConfig([
        createFakeOpenAIChatResponse([
            'content' => null,
            'tool_id' => 'call_1',
            'tool_name' => 'not_found_tool',
            'tool_args' => ['param' => 123],
        ]),
    ]);
    $llm = new OpenAILLM($config);
    $result = $llm->chat([Message::user('Test response')]);
    expect($result->toolCalls)->toHaveLength(1)
        ->and($result->toolCalls)
        ->{0}->toBeInstanceOf(ToolCall::class)
        ->{0}->id->toBe('call_1')
        ->{0}->name->toBe('not_found_tool')
        ->{0}->arguments->toMatchArray(['param' => 123]);
});
