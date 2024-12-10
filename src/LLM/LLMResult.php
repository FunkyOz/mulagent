<?php

declare(strict_types=1);

namespace MulAgent\LLM;

use MulAgent\Message\Message;
use MulAgent\Tool\ToolCall;

class LLMResult
{
    /**
     * @param  Message  $message
     * @param  array<ToolCall>  $toolCalls
     */
    public function __construct(
        readonly Message $message,
        readonly array $toolCalls = [],
    ) {
    }
}
