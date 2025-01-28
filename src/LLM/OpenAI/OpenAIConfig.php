<?php

declare(strict_types=1);

namespace MulAgent\LLM\OpenAI;

use OpenAI\Contracts\ClientContract;

final class OpenAIConfig
{
    /**
     * @param  string  $model
     * @param  string  $apiKey
     * @param  float|null  $temperature
     * @param  string|null  $organization
     * @param  string|null  $baseUrl
     * @param  array<string, string>  $headers
     * @param  ClientContract|null  $client
     */
    private function __construct(
        readonly string $model,
        readonly string $apiKey,
        readonly ?float $temperature = null,
        readonly ?string $organization = null,
        readonly ?string $baseUrl = null,
        readonly array $headers = [],
        readonly ?ClientContract $client = null,
    ) {
    }

    /**
     * @param  array{
     *     model?: string|null,
     *     temperature?: float|null,
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
        $model = $config['model'] ?? 'gpt-4o-mini';
        $apiKey = $config['api_key'] ?? (getenv('OPENAI_API_KEY') ?: '');
        $temperature = $config['temperature'] ?? null;
        $organization = $config['organization'] ?? (getenv('OPENAI_ORGANIZATION') ?: null);
        $baseUrl = $config['base_url'] ?? (getenv('OPENAI_BASE_URL') ?: null);
        $headers = $config['headers'] ?? [];
        $client = $config['client'] ?? null;
        return new self($model, $apiKey, $temperature, $organization, $baseUrl, $headers, $client);
    }
}
