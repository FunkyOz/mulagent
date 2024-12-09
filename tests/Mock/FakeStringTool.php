<?php

declare(strict_types=1);

namespace Tests\Mock;

use Mulagent\Tool\ToolCall;
use Mulagent\Tool\ToolDefinition;
use Mulagent\Tool\ToolInterface;
use Mulagent\Tool\ToolOutput;
use Mulagent\Utility\Utility;

class FakeStringTool implements ToolInterface
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
        $strParameters = Utility::jsonEncode($toolCall->arguments);
        return new ToolOutput(
            'Tool output with parameters: '.$strParameters,
            $toolCall->name,
        );
    }

}
