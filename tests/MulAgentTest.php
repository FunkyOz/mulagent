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
    expect($response->messages)->toHaveLength(3)
        ->and($response->messages)
        ->{0}->content->{0}->text->toBe('Run this tool!')
        ->{0}->role->toBe(MessageRole::ASSISTANT)
        ->{1}->content->{0}->text->toBe('Error: Tool "not_found_tool" not found.')
        ->{1}->role->toBe(MessageRole::TOOL)
        ->{2}->content->{0}->text->toBe('Response after tool call')
        ->{2}->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should handle mapped tool returning a string', function () {
    $llm = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Run this tool!',
            'tool_id' => 'call_1',
            'tool_name' => 'fake_tool',
            'tool_args' => ['param2' => '321', 'param1' => 123],
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool call',
        ]),
    ]);
    $agent = createFakeAgent('Lorenzo', $llm, null, [new FakeTool()]);
    $agentRunner = createFakeAgentRunner([$agent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->messages)->toHaveLength(3)
        ->and($response->messages)
        ->{0}->content->{0}->text->toBe('Run this tool!')
        ->{0}->role->toBe(MessageRole::ASSISTANT)
        ->{1}->content->{0}->text->toBe('Params passed: param1=123, param2=321')
        ->{1}->role->toBe(MessageRole::TOOL)
        ->{2}->content->{0}->text->toBe('Response after tool call')
        ->{2}->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should handle mapped tool returning an agent', function () {
    $llm = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Run this tool!',
            'tool_id' => 'call_1',
            'tool_name' => 'transfer_to_andrea_agent',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool call',
        ]),
    ]);
    $lorenzoAgent = createFakeAgent('Lorenzo', $llm);
    $andreaAgent = createFakeAgent('Andrea', $llm);
    $agentRunner = createFakeAgentRunner([$lorenzoAgent, $andreaAgent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->messages)->toHaveLength(3)
        ->and($response->messages)
        ->{0}->content->{0}->text->toBe('Run this tool!')
        ->{0}->role->toBe(MessageRole::ASSISTANT)
        ->{1}->content->{0}->text->toBe('successfully transferred')
        ->{1}->role->toBe(MessageRole::TOOL)
        ->{2}->content->{0}->text->toBe('Response after tool call')
        ->{2}->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool call');
});

it('should bounce agent to agent', function () {
    $fakeLLM = createFakeOpenAILLM([
        createFakeOpenAIChatResponse([
            'content' => 'Andrea tool',
            'tool_id' => 'call_1',
            'tool_name' => 'transfer_to_andrea_agent',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Lorenzo tool',
            'tool_id' => 'call_2',
            'tool_name' => 'transfer_to_lorenzo_agent',
        ]),
        createFakeOpenAIChatResponse([
            'content' => 'Response after tool calls',
        ]),
    ]);
    $lorenzoAgent = createFakeAgent('Lorenzo', $fakeLLM);
    $andreaAgent = createFakeAgent('Andrea', $fakeLLM);
    $agentRunner = createFakeAgentRunner([$lorenzoAgent, $andreaAgent]);
    $response = $agentRunner->run([Message::user('My message')]);
    expect($response->messages)->toHaveLength(5)
        ->and($response->messages)
        ->{0}->content->{0}->text->toBe('Andrea tool')
        ->{0}->role->toBe(MessageRole::ASSISTANT)
        ->{1}->content->{0}->text->toBe('successfully transferred')
        ->{1}->role->toBe(MessageRole::TOOL)
        ->{2}->content->{0}->text->toBe('Lorenzo tool')
        ->{2}->role->toBe(MessageRole::ASSISTANT)
        ->{3}->content->{0}->text->toBe('successfully transferred')
        ->{3}->role->toBe(MessageRole::TOOL)
        ->{4}->content->{0}->text->toBe('Response after tool calls')
        ->{4}->role->toBe(MessageRole::ASSISTANT)
        ->and($response->toString())->toBe('Response after tool calls');
});
