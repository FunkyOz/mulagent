<?php

use Anthropic\Testing\ClientFake;
use MulAgent\LLM\Anthropic\AnthropicConfig;

it('should parse anthropic config', function (array $config) {
    $anthropicConfig = AnthropicConfig::create($config);
    expect($anthropicConfig)
        ->model->toBe($config['model'] ?? 'claude-3-5-haiku-20241022')
        ->apiKey->toBe($config['api_key'] ?? '')
        ->maxTokens->toBe($config['max_tokens'] ?? 1024)
        ->temperature->toBe($config['temperature'] ?? null)
        ->baseUrl->toBe($config['base_url'] ?? null)
        ->headers->toMatchArray($config['headers'] ?? []);
})->with([
    [[]],
    [
        [
            'model' => 'claude-3-5-haiku-20241022',
            'api_key' => 'foo',
            'temperature' => 1.0,
            'max_tokens' => 2035,
            'base_url' => 'http://localhost',
            'headers' => ['Content-Type' => 'application/json'],
        ],
    ],
    [
        [
            'client' => new ClientFake(),
        ],
    ],
]);

it('should throw assertion exception', function (array $invalidConfig) {
    $defaultData = [
        'model' => 'gpt-4o',
        'api_key' => 'foo',
        'temperature' => 1.0,
        'max_tokens' => 1024,
        'base_url' => 'http://localhost',
        'headers' => []
    ];
    $config = array_merge($defaultData, $invalidConfig);
    AnthropicConfig::create($config);
})->with([
    [['model' => 1]],
    [['api_key' => true]],
    [['temperature' => 'string']],
    [['max_tokens' => 't']],
    [['base_url' => 1]],
    [['headers' => new stdClass()]],
])->throws(TypeError::class);
