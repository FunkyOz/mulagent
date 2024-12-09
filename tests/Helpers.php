<?php

use Mulagent\Agent\Agent;
use Mulagent\Agent\AgentRunner;
use Mulagent\LLM\OpenAI\OpenAIConfig;
use Mulagent\LLM\OpenAI\OpenAILLM;
use Mulagent\Utility\Utility;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;
use Tests\Mock\FakeAgentTool;

function createFakeOpenAIChatResponse(array $response): CreateResponse
{
    $message = [
        'content' => $response['content'] ?? null,
    ];
    if (isset($response['tool_id']) && isset($response['tool_name'])) {
        $message['tool_calls'] = [
            [
                'id' => $response['tool_id'],
                'type' => 'function',
                'function' => [
                    'name' => $response['tool_name'],
                    'arguments' => Utility::jsonEncode($response['tool_args'] ?? [])
                ],
            ]
        ];
    }
    return CreateResponse::fake(['choices' => [['message' => $message]]]);
}

function createFakeOpenAIConfig(array $responses): OpenAIConfig
{
    return OpenAIConfig::create(['client' => new ClientFake($responses)]);
}

function createFakeOpenAILLM(array $responses): OpenAILLM
{
    $config = createFakeOpenAIConfig($responses);
    return new OpenAILLM($config);
}

function createFakeAgent(string $name, OpenAILLM $llm, ?string $instructions = null, array $tools = []): Agent
{
    $agent = new Agent($name, $llm, $instructions, $tools);
    $agent->tools = $tools;
    return $agent;
}

function createFakeAgentRunner(array $agents): AgentRunner
{
    foreach ($agents as $agent1) {
        foreach ($agents as $agent2) {
            if ($agent1->name !== $agent2->name) {
                $agent1->tools = array_merge($agent1->tools, [new FakeAgentTool($agent2)]);
            }
        }
    }
    return new AgentRunner($agents[0]);
}

function assertArrayEqual(array $value, array $expected): void
{
    expect($value)->toHaveSameSize($expected);
    foreach ($value as $k => $v) {
        expect($expected)->toHaveKey($k);
        $expectedV = $expected[$k];
        expect(gettype($v))->toBe(gettype($expectedV));
        if (is_array($v)) {
            assertArrayEqual($v, $expected[$k]);
        } elseif (is_object($v)) {
            expect($v::class)->toBe($expectedV::class)
                ->and($v)->not->toBe($expectedV);
        } else {
            expect($v)->toBe($expectedV);
        }
    }
}
