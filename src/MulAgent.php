<?php

declare(strict_types=1);

namespace MulAgent;

use MulAgent\Agent\Agent;
use MulAgent\Agent\AgentResponse;
use MulAgent\Message\Message;
use MulAgent\Tool\ToolFormatter;
use MulAgent\Tool\ToolCall;
use RuntimeException;
use Stringable;
use Throwable;

final class MulAgent
{
    public function __construct(private readonly Agent $agent)
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
                    $output = $toolMap[$toolCall->name](...$toolCall->arguments);
                    if ($output instanceof Agent) {
                        $activeAgent = $output;
                        $output = 'successfully transferred';
                    } elseif ($output instanceof Stringable) {
                        $output = $output->__toString();
                    } elseif (!is_string($output)) {
                        throw new RuntimeException();
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
        $activeAgent = $this->agent;
        $history = $messages;
        if ($activeAgent->instruction !== null && $activeAgent->instruction !== '') {
            $history = array_merge(
                [Message::system($activeAgent->instruction)],
                array_filter($history, fn (Message $message) => !$message->isSystem())
            );
        }
        $historyInitLen = count($history);
        while (count($history) - $historyInitLen < $maxTurns) {
            $toolMap = self::parseToolMap($activeAgent->getTools());
            $llmResult = $activeAgent->llm->chat($history, $activeAgent->getTools());
            $history[] = $llmResult->message;
            $agentMessages[] = $llmResult->message;
            if (count($llmResult->toolCalls) === 0 || !$executeTools) {
                break;
            }
            [$partialMessages, $partialAgent] = self::handleToolCalls($llmResult->toolCalls, $toolMap);
            $history = array_merge($history, $partialMessages);
            $agentMessages = array_merge($agentMessages, $partialMessages);
            if (null !== $partialAgent) {
                $activeAgent = $partialAgent;
            }
        }
        return new AgentResponse($agentMessages, $activeAgent);
    }

    /**
     * @param  array<callable&object>  $tools
     * @return array<string, callable&object>
     */
    private static function parseToolMap(array $tools): array
    {
        $toolMap = [];
        foreach ($tools as $tool) {
            $toolMap[ToolFormatter::getName($tool)] = $tool;
        }
        return $toolMap;
    }
}
