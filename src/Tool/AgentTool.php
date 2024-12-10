<?php

declare(strict_types=1);

namespace MulAgent\Tool;

use MulAgent\Agent\Agent;

final class AgentTool implements ToolInterface
{
    public function __construct(
        readonly Agent $agent,
        private readonly ?string $toolName = null,
        private readonly ?string $toolDescription = null,
    ) {
    }

    public function getDefinition(): ToolDefinition
    {
        $agentName = preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($this->agent->name));
        return new ToolDefinition(
            $this->toolName ?? 'transfer_to_'.$agentName,
            $this->toolDescription ?? 'Transfer the conversation to agent '.$this->agent->name,
        );
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        return new ToolOutput(
            'assistant: '.$this->agent->name,
            $toolCall->name,
            $this->agent,
        );
    }
}
