<?php

declare(strict_types=1);

namespace Mulagent\LLM;

use Mulagent\Message\Message;
use Mulagent\Tool\ToolDefinition;

interface LLMInterface
{
    /**
     * @param  array<Message>  $messages
     * @param  array<ToolDefinition>  $tools
     * @return LLMResult
     */
    public function chat(array $messages = [], array $tools = []): LLMResult;
}
