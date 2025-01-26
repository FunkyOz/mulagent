<?php

declare(strict_types=1);

namespace MulAgent\Tool;

interface Tool
{
    public function getDefinition(): ToolDefinition;

    public function run(ToolCall $toolCall): ToolOutput;
}
