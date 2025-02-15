<?php

declare(strict_types=1);

namespace MulAgent;

use MulAgent\Agent\Agent;
use MulAgent\Agent\AgentResponse;
use MulAgent\Exceptions\ExceptionFactory;
use MulAgent\Message\Message;
use MulAgent\Tool\ToolFormatter;
use MulAgent\Tool\ToolCall;
use Stringable;
use Throwable;

final class MulAgent
{
    public function __construct(private Agent $activeAgent)
    {
    }

    /**
     * @param  array<ToolCall>  $toolCalls
     * @param  array<string, callable&object>  $toolMap
     * @return array{0: array<Message>, 1: Agent|null}
     */
    private static function handleToolCalls(array $toolCalls, array $toolMap): array
    {
        $messages = [];
        $activeAgent = null;
        foreach ($toolCalls as $toolCall) {
            if (!isset($toolMap[$toolCall->name])) {
                $message = Message::tool(
                    sprintf('Error: Tool "%s" not found.', $toolCall->name),
                    ['tool_call_id' => $toolCall->id]
                );
            } else {
                try {
                    $tool = $toolMap[$toolCall->name];
                    $output = $tool(...$toolCall->arguments);
                    if ($output instanceof Agent) {
                        $activeAgent = $output;
                        $output = 'successfully transferred';
                    } elseif ($output instanceof Stringable) {
                        $output = $output->__toString();
                    } elseif (is_scalar($output)) {
                        $output = (string) $output;
                    } else {
                        throw ExceptionFactory::createToolExecutionException('Tool output must be a valid Stringable object or a scalar type');
                    }
                } catch (Throwable $ex) {
                    $output = sprintf('Run tool "%s" failed: %s', $toolCall->name, $ex->getMessage());
                }
                $message = Message::tool($output, ['tool_call_id' => $toolCall->id]);
            }
            $messages[] = $message;
        }
        return [$messages, $activeAgent];
    }

    /**
     * @param  array<Message>  $messages
     * @param  int  $maxTurns
     * @param  bool  $executeTools
     * @return AgentResponse
     */
    public function run(
        array $messages = [],
        int $maxTurns = PHP_INT_MAX,
        bool $executeTools = true
    ): AgentResponse {
        $agentMessages = [];
        $history = $messages;
        $historyInitLen = count($history);
        while (count($history) - $historyInitLen < $maxTurns) {
            if (!empty($this->activeAgent->instruction)) {
                $history = array_merge(
                    [Message::system($this->activeAgent->instruction)],
                    array_filter($history, fn (Message $message) => !$message->isSystem())
                );
            }
            $toolMap = self::parseToolMap($this->activeAgent->getTools());
            $llmResult = $this->activeAgent->llm->chat($history, $this->activeAgent->getTools());
            $history[] = $llmResult->message;
            $agentMessages[] = $llmResult->message;
            if (count($llmResult->toolCalls) === 0 || !$executeTools) {
                break;
            }
            [$partialMessages, $activeAgent] = self::handleToolCalls($llmResult->toolCalls, $toolMap);
            $history = array_merge($history, $partialMessages);
            $agentMessages = array_merge($agentMessages, $partialMessages);
            if (null !== $activeAgent) {
                $this->activeAgent = $activeAgent;
            }
        }
        return new AgentResponse($agentMessages, $this->activeAgent);
    }

    /**
     * @param  array<callable&object>  $tools
     * @return array<string, callable&object>
     */
    private static function parseToolMap(array $tools): array
    {
        $toolMap = [];
        foreach ($tools as $tool) {
            $toolMap[ToolFormatter::getToolName($tool)] = $tool;
        }
        return $toolMap;
    }
}
