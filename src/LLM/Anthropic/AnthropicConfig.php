<?php

declare(strict_types=1);

namespace MulAgent\LLM\Anthropic;

use Anthropic\Contracts\ClientContract;
use MulAgent\Exceptions\ExceptionFactory;

final class AnthropicConfig
{
    /**
     * @param  string  $model
     * @param  string  $apiKey
     * @param  int  $maxTokens
     * @param  float|null  $temperature
     * @param  string|null  $baseUrl
     * @param  array<string, string>  $headers
     * @param  ClientContract|null  $client
     */
    private function __construct(
        readonly string $model,
        readonly string $apiKey,
        readonly int $maxTokens,
        readonly ?float $temperature = null,
        readonly ?string $baseUrl = null,
        readonly array $headers = [],
        readonly ?ClientContract $client = null,
    ) {
    }

    /**
     * @param  array{
     *     model?: string|null,
     *     max_tokens?: int|null,
     *     temperature?: float|null,
     *     api_key?: string|null,
     *     base_url?: string|null,
     *     headers?: array<string, string>|null,
     *     client?: ClientContract|null,
     * }  $config
     * @return AnthropicConfig
     */
    public static function create(array $config = []): AnthropicConfig
    {
        if (!class_exists('\Anthropic')) {
            throw ExceptionFactory::createAnthropicDisabledException();
        }
        $model = $config['model'] ?? 'claude-3-5-haiku-20241022';
        $apiKey = $config['api_key'] ?? (getenv('ANTHROPIC_API_KEY') ?: '');
        $maxTokens = $config['max_tokens'] ?? 1024;
        $temperature = $config['temperature'] ?? null;
        $baseUrl = $config['base_url'] ?? (getenv('ANTHROPIC_BASE_URL') ?: null);
        $headers = $config['headers'] ?? [];
        $client = $config['client'] ?? null;
        return new self($model, $apiKey, $maxTokens, $temperature, $baseUrl, $headers, $client);
    }
}
