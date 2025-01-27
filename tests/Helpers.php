<?php

use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\MulAgent;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

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
                    'arguments' => json_encode($response['tool_args'] ?? [], JSON_THROW_ON_ERROR)
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
    return new Agent($name, $llm, $instructions, $tools);
}

/**
 * @param  array<Agent>  $agents
 * @return MulAgent
 */
function createFakeAgentRunner(array $agents): MulAgent
{
    foreach ($agents as $agent1) {
        foreach ($agents as $agent2) {
            if ($agent1->name !== $agent2->name) {
                $agent1->addTools([$agent2]);
            }
        }
    }
    return new MulAgent($agents[0]);
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
