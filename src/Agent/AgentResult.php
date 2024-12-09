<?php

declare(strict_types=1);

namespace Mulagent\Agent;

use Mulagent\Message\Message;
use Mulagent\Tool\ToolOutput;

class AgentResult
{
    public function __construct(
        readonly Message $message,
        readonly ?ToolOutput $toolOutput = null,
    ) {
    }
}
