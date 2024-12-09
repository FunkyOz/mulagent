<?php

declare(strict_types=1);

namespace Mulagent\Agent;

use Mulagent\Message\Message;
use Mulagent\Tool\ToolCall;
use Mulagent\Tool\ToolDefinition;
use Mulagent\Tool\ToolInterface;
use Mulagent\Utility\Utility;

class AgentRunner
{
    public function __construct(private readonly Agent $agent)
    {
    }

    /**
     * @param  array<ToolCall>  $toolCalls
     * @param  array<string, ToolInterface>  $toolMap
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
                    $toolCall->id
                );
                $agentResults[] = new AgentResult($message);
            } else {
                $toolOutput = $toolMap[$toolCall->name]->run($toolCall);
                $message = Message::tool($toolOutput->content, $toolCall->id);
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
     * @param  float  $maxTurns
     * @param  bool  $executeTools
     * @return AgentResponse
     */
    public function run(
        array $messages = [],
        float $maxTurns = INF,
        bool $executeTools = true
    ): AgentResponse {
        $agentResults = [];
        $activeAgent = $this->agent;
        $history = Utility::arrayClone($messages);
        if ($activeAgent->instruction !== null && $activeAgent->instruction !== '') {
            $history = array_merge([Message::system($activeAgent->instruction)], $history);
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
            [$partialAgentResults, $partialAgent] = self::handleToolCalls($llmResult->toolCalls, $toolMap);
            $partialMessages = array_map(fn (AgentResult $result) => $result->message, $partialAgentResults);
            $history = array_merge($history, $partialMessages);
            $agentResults = array_merge($agentResults, $partialAgentResults);
            if (null !== $partialAgent) {
                $activeAgent = $partialAgent;
            }
        }
        return new AgentResponse($agentResults, $activeAgent);
    }

    /**
     * @param  array<ToolInterface>  $tools
     * @return array{0: array<string, ToolInterface>, 1: array<ToolDefinition>}
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
