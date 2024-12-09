<?php

declare(strict_types=1);

namespace Tests\Mock;

use Mulagent\Agent\Agent;
use Mulagent\Tool\ToolCall;
use Mulagent\Tool\ToolDefinition;
use Mulagent\Tool\ToolInterface;
use Mulagent\Tool\ToolOutput;

class FakeAgentTool implements ToolInterface
{
    public function __construct(readonly Agent $agent)
    {
    }

    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition(
            $this->agent->name,
            'A tool returning an agent for testing'
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
