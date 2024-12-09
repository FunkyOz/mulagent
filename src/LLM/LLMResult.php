<?php

declare(strict_types=1);

namespace Mulagent\LLM;

use Mulagent\Message\Message;
use Mulagent\Tool\ToolCall;

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
