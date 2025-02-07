<?php

declare(strict_types=1);

namespace MulAgent\LLM\Anthropic;

use Anthropic;
use Anthropic\Contracts\ClientContract;
use MulAgent\Exceptions\ExceptionFactory;
use MulAgent\LLM\LLM;
use MulAgent\LLM\LLMResult;
use MulAgent\Message\Content;
use MulAgent\Message\ContentType;
use MulAgent\Message\Message;
use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolFormatter;
use Throwable;

final class AnthropicLLM implements LLM
{
    private ClientContract $client;
    private readonly string $model;
    private readonly int $maxTokens;
    private readonly ?float $temperature;

    public function __construct(?AnthropicConfig $config = null)
    {
        if (!class_exists('\Anthropic')) {
            throw ExceptionFactory::createAnthropicDisabledException();
        }
        if ($config?->client) {
            $this->client = $config->client;
        } else {
            $factory = Anthropic::factory()
                ->withApiKey($config?->apiKey ?? '')
                ->withBaseUri($config?->baseUrl ?? '');
            $headers = array_merge([
                'anthropic-version' => '2023-06-01',
            ], $config?->headers ?? []);
            foreach ($headers as $name => $value) {
                $factory = $factory->withHttpHeader($name, $value);
            }
            $this->client = $factory->make();
        }
        $this->model = $config?->model ?? 'claude-3-5-haiku-20241022';
        $this->temperature = $config?->temperature;
        $this->maxTokens = $config?->maxTokens ?? 1024;
    }

    /**
     * @param  array<Message>  $messages
     * @param  array<callable&object>  $tools
     * @return LLMResult
     */
    public function chat(array $messages = [], array $tools = []): LLMResult
    {
        [$realMessages, $system] = self::mapToAnthropicMessage($messages);
        $parameters = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $system,
        ];
        if (null !== $system) {
            $parameters['system'] = $system;
        }
        if (null !== $this->temperature) {
            $parameters['temperature'] = $this->temperature;
        }
        if (count($realMessages) > 0) {
            $parameters['messages'] = $realMessages;
        }
        if (count($tools) > 0) {
            $parameters['tools'] = array_map(
                function ($tool) {
                    $toolArray = ToolFormatter::formatToolAsJsonSchema($tool);
                    $toolArray['input_schema'] = $toolArray['parameters'] ?? [];
                    unset($toolArray['parameters'], $toolArray['strict']);
                    return $toolArray;
                },
                $tools
            );
        }
        try {
            $response = $this->client->messages()->create($parameters);
        } catch (Throwable $ex) {
            throw ExceptionFactory::createLLMBadRequestException($ex->getMessage(), $parameters);
        }

        $toolCalls = [];
        $rawToolCalls = [];
        $assistantMsg = '';
        foreach ($response->content as $content) {
            if ('tool_use' === $content->type && null !== $content->id && null !== $content->name) {
                $rawToolCalls[] = $content->toArray();
                $toolCalls[] = new ToolCall(
                    $content->id,
                    $content->name,
                    $content->input ?? [],
                );
            }
            $assistantMsg .= ($content->text ?? '');
        }
        $additionalArgs = count($rawToolCalls) > 0 ? ['tool_calls' => $rawToolCalls] : [];
        return new LLMResult(
            Message::assistant($assistantMsg, $additionalArgs),
            $toolCalls
        );
    }

    /**
     * @param  array<Message>  $messages
     * @return array{0: array<mixed>, 1: string|null}
     */
    private static function mapToAnthropicMessage(array $messages): array
    {
        $realMessages = [];
        $system = null;
        foreach ($messages as $message) {
            if ($message->isSystem()) {
                $system = $message->content[0]->getValue();
                continue;
            }
            if ($message->isTool() && null !== ($toolCallId = $message->additionalArgs['tool_call_id'] ?? null)) {
                $realMessages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolCallId,
                            'content' => $message->content[0]->getValue(),
                        ]
                    ],
                ];
            } elseif ($message->isAssistant() && null !== ($toolCalls = (array)($message->additionalArgs['tool_calls'] ?? []))) {
                $realMessages[] = [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $message->content[0]->getValue(),
                        ],
                        ...$toolCalls,
                    ],
                ];
            } else {
                $realMessages[] = [
                    'role' => $message->role->value,
                    'content' => array_map(function (Content $content) {
                        switch ($content->getType()) {
                            case ContentType::TEXT:
                                return ['type' => 'text', 'text' => $content->getValue()];
                            case ContentType::IMAGE:
                                [$base64, $mediaType] = self::convertToBase64($content);
                                return [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mediaType,
                                        'data' => $base64,
                                    ]
                                ];
                        }
                    }, $message->content),
                ];
            }
        }
        return [$realMessages, $system];
    }

    /**
     * @param  Content  $content
     * @return array{0: string, 1: string}
     */
    private static function convertToBase64(Content $content): array
    {
        $data = file_get_contents($content->getValue());
        $mimeType = mime_content_type($content->getValue());
        if (false === $data || false === $mimeType) {
            // TODO
            throw new \RuntimeException('Unable to read image content');
        }
        return [base64_encode($data), $mimeType];
    }
}
