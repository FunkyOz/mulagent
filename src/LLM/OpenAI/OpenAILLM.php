<?php

declare(strict_types=1);

namespace Mulagent\LLM\OpenAI;

use JsonException;
use Mulagent\Exceptions\ExceptionFactory;
use Mulagent\LLM\LLMInterface;
use Mulagent\LLM\LLMResult;
use Mulagent\Message\Message;
use Mulagent\Tool\ToolCall;
use Mulagent\Tool\ToolDefinition;
use Mulagent\Tool\ToolFormatter;
use Mulagent\Utility\Utility;
use OpenAI;
use OpenAI\Contracts\ClientContract;

final class OpenAILLM implements LLMInterface
{
    private readonly ClientContract $client;

    private readonly string $model;

    private readonly ?int $temperature;

    public function __construct(?OpenAIConfig $config = null)
    {
        if ($config?->client) {
            $this->client = $config->client;
        } else {
            $factory = OpenAI::factory()
                ->withApiKey($config?->apiKey ?? (getenv('OPENAI_API_KEY') ?: ''))
                ->withBaseUri($config?->baseUrl ?? (getenv('OPENAI_BASE_URL') ?: ''))
                ->withOrganization($config?->organization ?? (getenv('OPENAI_ORGANIZATION') ?: ''));
            $headers = $config?->headers ?? [];
            if (count($headers) > 0) {
                foreach ($headers as $name => $value) {
                    $factory = $factory->withHttpHeader($name, $value);
                }
            }
            $this->client = $factory->make();
        }
        $this->model = $config?->model ?? 'gpt-4o';
        $this->temperature = $config?->temperature;
    }

    /**
     * @param  array<Message>  $messages
     * @param  array<ToolDefinition>  $tools
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
            $parameters['messages'] = self::mapMessagesToArray($messages);
        }
        if (count($tools) > 0) {
            $parameters['tools'] = array_map(
                fn(ToolDefinition $tool) => ToolFormatter::formatToolDefinitionAsJsonSchema($tool),
                $tools
            );
        }
        $response = $this->client->chat()->create($parameters);
        if (!isset($response->choices[0])) {
            throw ExceptionFactory::createInvalidResponseException('Error: invalid response choice');
        }
        $toolCalls = [];
        foreach ($response->choices[0]->message->toolCalls as $toolCall) {
            $toolCalls[] = new ToolCall(
                $toolCall->id,
                $toolCall->function->name,
                Utility::jsonDecode($toolCall->function->arguments),
            );
        }

        return new LLMResult(
            Message::assistant($response->choices[0]->message->content ?? ''),
            $toolCalls
        );
    }

    /**
     * @param  array<Message>  $messages
     * @return array<array{ role: string, content: string, tool_call_id?: string }>
     */
    private static function mapMessagesToArray(array $messages): array
    {
        return array_map(function (Message $message): array {
            $openAIMessage = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
            if ($message->isTool()) {
                $openAIMessage['tool_call_id'] = $message->toolId ?? '';
            }
            return $openAIMessage;
        }, $messages);
    }
}
