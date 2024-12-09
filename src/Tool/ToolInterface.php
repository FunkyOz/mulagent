<?php

declare(strict_types=1);

namespace Mulagent\Tool;

interface ToolInterface
{
    public function getDefinition(): ToolDefinition;

    public function run(ToolCall $toolCall): ToolOutput;
}
