<?php

declare(strict_types=1);

namespace MulAgent\LLM\OpenAI;

use JsonException;
use MulAgent\Exceptions\ExceptionFactory;
use MulAgent\LLM\LLM;
use MulAgent\LLM\LLMResult;
use MulAgent\Message\Content;
use MulAgent\Message\ContentType;
use MulAgent\Message\Message;
use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolFormatter;
use OpenAI;
use OpenAI\Contracts\ClientContract;
use Throwable;

final class OpenAILLM implements LLM
{
    private ClientContract $client;

    private readonly string $model;

    private readonly ?float $temperature;

    public function __construct(?OpenAIConfig $config = null)
    {
        if ($config?->client) {
            $this->client = $config->client;
        } else {
            $factory = OpenAI::factory()
                ->withApiKey($config?->apiKey ?? '')
                ->withBaseUri($config?->baseUrl ?? '')
                ->withOrganization($config?->organization);
            $headers = $config?->headers ?? [];
            foreach ($headers as $name => $value) {
                $factory = $factory->withHttpHeader($name, $value);
            }
            $this->client = $factory->make();
        }
        $this->model = $config?->model ?? 'gpt-4o-mini';
        $this->temperature = $config?->temperature;
    }

    /**
     * @param  array<Message>  $messages
     * @param  array<callable&object>  $tools
     *
     * @throws JsonException
     */
    public function chat(array $messages = [], array $tools = []): LLMResult
    {
        $parameters = [
            'model' => $this->model,
        ];
        if ($this->temperature !== null) {
            $parameters['temperature'] = $this->temperature;
        }
        if (count($messages) > 0) {
            $parameters['messages'] = self::mapToOpenAIMessages($messages);
        }
        if (count($tools) > 0) {
            $parameters['tools'] = array_map(
                fn ($tool) => [
                    'type' => 'function',
                    'function' => ToolFormatter::formatToolAsJsonSchema($tool),
                ],
                $tools
            );
        }
        try {
            $response = $this->client->chat()->create($parameters);
        } catch (Throwable $ex) {
            throw ExceptionFactory::createLLMBadRequestException($ex->getMessage(), $parameters);
        }
        $choices = $response->choices;
        if (count($choices) !== 1) {
            throw ExceptionFactory::createInvalidResponseException('Error: invalid response choices');
        }
        $choice = end($choices);
        $toolCalls = [];
        $rawToolCalls = [];
        foreach ($choice->message->toolCalls as $toolCall) {
            $rawToolCalls[] = $toolCall->toArray();
            $toolCalls[] = new ToolCall(
                $toolCall->id,
                $toolCall->function->name,
                (array)json_decode($toolCall->function->arguments, true, 512, JSON_THROW_ON_ERROR),
            );
        }
        $additionalArgs = count($rawToolCalls) > 0 ? ['tool_calls' => $rawToolCalls] : [];
        return new LLMResult(
            Message::assistant($choice->message->content ?? '', $additionalArgs),
            $toolCalls
        );
    }

    /**
     * @param  array<Message>  $messages
     * @return array<mixed>
     */
    private static function mapToOpenAIMessages(array $messages): array
    {
        return array_map(function (Message $message): array {
            $openAIMessage = [
                'role' => $message->role->value,
                'content' => array_map(function (Content $content) {
                    $type = match ($content->getType()) {
                        ContentType::TEXT => 'text',
                        ContentType::IMAGE => 'image_url',
                    };
                    return ['type' => $type, $type => $content->getValue()];
                }, $message->content),
            ];
            return array_merge($openAIMessage, $message->additionalArgs);
        }, $messages);
    }
}
