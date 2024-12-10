<?php

declare(strict_types=1);

namespace MulAgent\LLM;

use MulAgent\Message\Message;
use MulAgent\Tool\ToolDefinition;

interface LLMInterface
{
    /**
     * @param  array<Message>  $messages
     * @param  array<ToolDefinition>  $tools
     * @return LLMResult
     */
    public function chat(array $messages = [], array $tools = []): LLMResult;
}
