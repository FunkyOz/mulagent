<?php

declare(strict_types=1);

namespace MulAgent\LLM\OpenAI;

use MulAgent\Validation\Assert;
use OpenAI\Contracts\ClientContract;

final class OpenAIConfig
{
    /**
     * @param  string  $model
     * @param  int|null  $temperature
     * @param  string|null  $apiKey
     * @param  string|null  $organization
     * @param  string|null  $baseUrl
     * @param  array<string, string>  $headers
     * @param  ClientContract|null  $client
     */
    private function __construct(
        readonly string $model,
        readonly ?int $temperature = null,
        readonly ?string $apiKey = null,
        readonly ?string $organization = null,
        readonly ?string $baseUrl = null,
        readonly array $headers = [],
        readonly ?ClientContract $client = null,
    ) {
    }

    /**
     * @param  array{
     *     model?: string|null,
     *     temperature?: int|null,
     *     api_key?: string|null,
     *     organization?: string|null,
     *     base_url?: string|null,
     *     headers?: array<string, string>|null,
     *     client?: ClientContract|null,
     * }  $config
     * @return OpenAIConfig
     */
    public static function create(array $config = []): OpenAIConfig
    {
        $model = $config['model'] ?? 'gpt-4o';
        $temperature = $config['temperature'] ?? null;
        $apiKey = $config['api_key'] ?? null;
        $organization = $config['organization'] ?? null;
        $baseUrl = $config['base_url'] ?? null;
        $headers = $config['headers'] ?? [];
        $client = $config['client'] ?? null;
        Assert::lazy()
            ->that($model, 'model')->string()->notEmpty()
            ->that($temperature, 'temperature')->nullOr()->integer()->between(0, 2)
            ->that($apiKey, 'api_key')->nullOr()->string()->notEmpty()
            ->that($organization, 'organization')->nullOr()->string()->notEmpty()
            ->that($baseUrl, 'base_url')->nullOr()->url()
            ->that($headers, 'headers')->isArray()
            ->that($client, 'client')->nullOr()->isInstanceOf(ClientContract::class)
            ->verifyNow();
        return new self($model, $temperature, $apiKey, $organization, $baseUrl, $headers, $client);
    }
}
