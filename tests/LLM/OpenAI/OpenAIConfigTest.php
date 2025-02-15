<?php

use MulAgent\LLM\OpenAI\OpenAIConfig;
use OpenAI\Testing\ClientFake;

it('should parse openai config', function (array $config) {
    $openAIConfig = OpenAIConfig::create($config);
    expect($openAIConfig)
        ->model->toBe($config['model'] ?? 'gpt-4o-mini')
        ->apiKey->toBe($config['api_key'] ?? '')
        ->temperature->toBe($config['temperature'] ?? null)
        ->organization->toBe($config['organization'] ?? null)
        ->baseUrl->toBe($config['base_url'] ?? null)
        ->headers->toMatchArray($config['headers'] ?? []);
})->with([
    [[]],
    [
        [
            'model' => 'gpt-4o',
            'api_key' => 'foo',
            'temperature' => 1.0,
            'organization' => 'org',
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
        'organization' => 'org',
        'base_url' => 'http://localhost',
        'headers' => []
    ];
    $config = array_merge($defaultData, $invalidConfig);
    OpenAIConfig::create($config);
})->with([
    [['model' => 1]],
    [['api_key' => true]],
    [['temperature' => 'string']],
    [['organization' => 7.6]],
    [['base_url' => 1]],
    [['headers' => new stdClass()]],
])->throws(TypeError::class);
