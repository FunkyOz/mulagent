<?php

declare(strict_types=1);

namespace MulAgent;

use MulAgent\Agent\Agent;
use MulAgent\Agent\AgentResponse;
use MulAgent\Agent\AgentResult;
use MulAgent\Message\Message;
use MulAgent\Tool\Tool;
use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolDefinition;

final class MulAgent
{
    public function __construct(private readonly Agent $agent)
    {
    }

    /**
     * @param  array<ToolCall>  $toolCalls
     * @param  array<string, Tool>  $toolMap
     * @return array{0: array<AgentResult>, 1: Agent|null}
     */
    private static function handleToolCalls(
        array $toolCalls,
        array $toolMap,
    ): array {
        $agentResults = [];
        $activeAgent = null;
        foreach ($toolCalls as $toolCall) {
            if (!isset($toolMap[$toolCall->name])) {
                $message = Message::tool(
                    sprintf('Error: Tool "%s" not found.', $toolCall->name),
                    [
                        'tool_call_id' => $toolCall->id
                    ]
                );
                $agentResults[] = new AgentResult($message);
            } else {
                $toolOutput = $toolMap[$toolCall->name]->run($toolCall);
                $message = Message::tool($toolOutput->content, [
                    'tool_call_id' => $toolCall->id
                ]);
                if ($toolOutput->isAgent()) {
                    $activeAgent = $toolOutput->asAgent();
                }
                $agentResults[] = new AgentResult($message, $toolOutput);
            }
        }
        return [$agentResults, $activeAgent];
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
        $agentResults = [];
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
            [$toolMap, $tools] = self::parseToolMap($activeAgent->tools);
            $llmResult = $activeAgent->llm->chat($history, $tools);
            $history[] = $llmResult->message;
            $agentResults[] = new AgentResult($llmResult->message);
            if (count($llmResult->toolCalls) === 0 || !$executeTools) {
                break;
            }
            [$partialResults, $partialAgent] = self::handleToolCalls($llmResult->toolCalls, $toolMap);
            $partialMessages = array_map(fn (AgentResult $result) => $result->message, $partialResults);
            $history = array_merge($history, $partialMessages);
            $agentResults = array_merge($agentResults, $partialResults);
            if (null !== $partialAgent) {
                $activeAgent = $partialAgent;
            }
        }
        return new AgentResponse($agentResults, $activeAgent);
    }

    /**
     * @param  array<Tool>  $tools
     * @return array{0: array<string, Tool>, 1: array<ToolDefinition>}
     */
    private static function parseToolMap(array $tools): array
    {
        $toolInfos = [];
        $toolMap = [];
        foreach ($tools as $tool) {
            $toolInfos[] = $tool->getDefinition();
            $toolMap[$tool->getDefinition()->name] = $tool;
        }
        return [$toolMap, $toolInfos];
    }
}
