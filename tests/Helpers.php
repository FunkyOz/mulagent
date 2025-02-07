<?php

use Anthropic\Responses\Messages\CreateResponse as AnthropicCreateResponse;
use Anthropic\Testing\ClientFake as AnthropicClientFake;
use MulAgent\Agent\Agent;
use MulAgent\LLM\Anthropic\AnthropicConfig;
use MulAgent\LLM\Anthropic\AnthropicLLM;
use MulAgent\LLM\LLM;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\MulAgent;
use OpenAI\Responses\Chat\CreateResponse as OpenAICreateResponse;
use OpenAI\Testing\ClientFake as OpenAIClientFake;

function createFakeOpenAIChatResponse(array $response): OpenAICreateResponse
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
    return OpenAICreateResponse::fake(['choices' => [['message' => $message]]]);
}

function createFakeOpenAIConfig(array $responses): OpenAIConfig
{
    return OpenAIConfig::create(['client' => new OpenAIClientFake($responses)]);
}

function createFakeOpenAILLM(array $responses): OpenAILLM
{
    $config = createFakeOpenAIConfig($responses);
    return new OpenAILLM($config);
}

function createFakeAnthropicChatResponse(array $response): AnthropicCreateResponse
{
    $content = [];
    if (!empty($response['content'])) {
        $content[] = [
            'type' => 'text',
            'text' => $response['content']
        ];
    }
    if (isset($response['tool_id']) && isset($response['tool_name'])) {
        $content[] = [
            'type' => 'tool_use',
            'id' => $response['tool_id'],
            'name' => $response['tool_name'],
            'input' => $response['tool_args'] ?? [],
        ];
    }
    return AnthropicCreateResponse::fake(['content' => $content]);
}

function createFakeAnthropicConfig(array $responses): AnthropicConfig
{
    return AnthropicConfig::create(['client' => new AnthropicClientFake($responses)]);
}

function createFakeAnthropicLLM(array $responses): AnthropicLLM
{
    $config = createFakeAnthropicConfig($responses);
    return new AnthropicLLM($config);
}

function createFakeAgent(string $name, LLM $llm, ?string $instructions = null, array $tools = []): Agent
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
