<?php

use MulAgent\Message\Message;
use MulAgent\Message\MessageRole;
use Tests\Mock\FakeTool;

it('should handle not mapped tool', function () {
    $llm = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Run this tool!',
            'tool_id' => 'call_1',
            'tool_name' => 'not_found_tool',
            'tool_args' => ['param' => 123],
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool call',
        ]),
    ]);
    $agent = createFakeAgent('Lorenzo', $llm);
    $agentRunner = createFakeAgentRunner([$agent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->results)->toHaveLength(3)
        ->and($response->results[0]->toolOutput)->toBeNull()
        ->and($response->results[0]->message)
        ->content->{0}->text->toBe('Run this tool!')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->results[1]->toolOutput)->toBeNull()
        ->and($response->results[1]->message)
        ->content->{0}->text->toBe('Error: Tool "not_found_tool" not found.')
        ->role->toBe(MessageRole::TOOL)
        ->and($response->results[2]->toolOutput)->toBeNull()
        ->and($response->results[2]->message)
        ->content->{0}->text->toBe('Response after tool call')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should handle mapped tool returning a string', function () {
    $llm = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Run this tool!',
            'tool_id' => 'call_1',
            'tool_name' => 'my_tool',
            'tool_args' => ['param' => 123],
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool call',
        ]),
    ]);
    $agent = createFakeAgent('Lorenzo', $llm, null, [new FakeTool('my_tool')]);
    $agentRunner = createFakeAgentRunner([$agent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->results)->toHaveLength(3)
        ->and($response->results[0]->toolOutput)->toBeNull()
        ->and($response->results[0]->message)
        ->content->{0}->text->toBe('Run this tool!')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->results[1]->toolOutput)->not->toBeNull()
        ->and($response->results[1]->message)
        ->content->{0}->text->toBe('Tool output with parameters: {"param":123}')
        ->role->toBe(MessageRole::TOOL)
        ->and($response->results[2]->toolOutput)->toBeNull()
        ->and($response->results[2]->message)
        ->content->{0}->text->toBe('Response after tool call')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should handle mapped tool returning an agent', function () {
    $llm = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Run this tool!',
            'tool_id' => 'call_1',
            'tool_name' => 'transfer_to_andrea',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool call',
        ]),
    ]);
    $lorenzoAgent = createFakeAgent('Lorenzo', $llm);
    $andreaAgent = createFakeAgent('Andrea', $llm);
    $agentRunner = createFakeAgentRunner([$lorenzoAgent, $andreaAgent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->results)->toHaveLength(3)
        ->and($response->results[0]->toolOutput)->toBeNull()
        ->and($response->results[0]->message)
        ->content->{0}->text->toBe('Run this tool!')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->results[1]->toolOutput)->not->toBeNull()
        ->and($response->results[1]->message)
        ->content->{0}->text->toBe('assistant: Andrea')
        ->role->toBe(MessageRole::TOOL)
        ->and($response->results[2]->toolOutput)->toBeNull()
        ->and($response->results[2]->message)
        ->content->{0}->text->toBe('Response after tool call')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should bounce agent to agent', function () {
    $fakeLLM = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Andrea tool',
            'tool_id' => 'call_1',
            'tool_name' => 'transfer_to_andrea',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Lorenzo tool',
            'tool_id' => 'call_2',
            'tool_name' => 'transfer_to_lorenzo',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool calls',
        ]),
    ]);
    $lorenzoAgent = createFakeAgent('Lorenzo', $fakeLLM);
    $andreaAgent = createFakeAgent('Andrea', $fakeLLM);
    $agentRunner = createFakeAgentRunner([$lorenzoAgent, $andreaAgent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->results)->toHaveLength(5)
        ->and($response->results[0]->message)
        ->content->{0}->text->toBe('Andrea tool')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->results[1]->message)
        ->content->{0}->text->toBe('assistant: Andrea')
        ->role->toBe(MessageRole::TOOL)
        ->and($response->results[2]->message)
        ->content->{0}->text->toBe('Lorenzo tool')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->results[3]->message)
        ->content->{0}->text->toBe('assistant: Lorenzo')
        ->role->toBe(MessageRole::TOOL)
        ->and($response->results[4]->message)
        ->content->{0}->text->toBe('Response after tool calls')
        ->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool calls');
});
