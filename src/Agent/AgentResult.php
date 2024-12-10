<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use MulAgent\Message\Message;
use MulAgent\Tool\ToolOutput;

class AgentResult
{
    public function __construct(
        readonly Message $message,
        readonly ?ToolOutput $toolOutput = null,
    ) {
    }
}
