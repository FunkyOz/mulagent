<?php

declare(strict_types=1);

namespace Tests\Mock;

use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolDefinition;
use MulAgent\Tool\ToolInterface;
use MulAgent\Tool\ToolOutput;

class FakeTool implements ToolInterface
{
    public function __construct(readonly string $name)
    {
    }

    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition($this->name, 'A tool returning a string for testing');
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        $strParameters = json_encode($toolCall->arguments, JSON_THROW_ON_ERROR);
        return new ToolOutput(
            'Tool output with parameters: '.$strParameters,
            $toolCall->name,
        );
    }
}
