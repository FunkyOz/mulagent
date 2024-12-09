<?php

use Mulagent\Exceptions\AssertionException;
use Mulagent\Exceptions\LazyAssertionException;
use Mulagent\LLM\OpenAI\OpenAIConfig;
use OpenAI\Contracts\ClientContract;
use OpenAI\Testing\ClientFake;

it('should parse openai config without client', function (array $config) {
    $openAIConfig = OpenAIConfig::create($config);
    expect($openAIConfig)
        ->model->toBe($config['model'] ?? 'gpt-4o')
        ->apiKey->toBe($config['api_key'] ?? null)
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
    try {
        OpenAIConfig::create([
            'model' => 1,
            'api_key' => true,
            'temperature' => 3,
            'organization' => 7.6,
            'base_url' => 'not an url',
            'headers' => new stdClass(),
        ]);
    } catch (Throwable $e) {
        expect($e)->toBeInstanceOf(LazyAssertionException::class)
            ->getErrorExceptions()
            ->each
            ->toBeInstanceOf(AssertionException::class);
        $errors = array_map(fn (AssertionException $ex) => (string) $ex, $e->getErrorExceptions());
        expect($errors)->toEqual([
            'Value "1" expected to be string, type integer given.',
            'Provided "3" is neither greater than or equal to "0" nor less than or equal to "2".',
            'Value "<TRUE>" expected to be string, type boolean given.',
            'Value "7.6" expected to be string, type double given.',
            'Value "not an url" was expected to be a valid URL starting with http or https',
            'Value "stdClass" is not an array.',
        ]);
    }
});
