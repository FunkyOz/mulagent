<?php

use MulAgent\LLM\OpenAI\OpenAIConfig;
use OpenAI\Contracts\ClientContract;
use OpenAI\Testing\ClientFake;

it('should parse openai config without client', function (array $config) {
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
            'temperature' => 1,
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

it('should parse openai config with client', function () {
    $stubClient = Mockery::mock(ClientContract::class);
    OpenAIConfig::create([
        'client' => $stubClient,
    ]);
})->throwsNoExceptions();

it('should throw assertion exception', function () {
    OpenAIConfig::create([
        'model' => 1,
        'api_key' => true,
        'temperature' => 3,
        'organization' => 7.6,
        'base_url' => 'not an url',
        'headers' => new stdClass(),
    ]);
})->throws(TypeError::class);
