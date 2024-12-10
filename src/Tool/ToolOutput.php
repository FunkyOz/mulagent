<?php

declare(strict_types=1);

namespace MulAgent\Tool;

use MulAgent\Agent\Agent;
use MulAgent\Exceptions\ExceptionFactory;

final class ToolOutput
{
    public function __construct(
        readonly string $content,
        readonly string $toolName,
        readonly mixed $output = null,
    ) {
    }

    public function isAgent(): bool
    {
        return $this->output instanceof Agent;
    }

    public function asAgent(): Agent
    {
        if (!($this->output instanceof Agent)) {
            throw ExceptionFactory::createAgentCastingException('Cannot cast to agent');
        }
        return $this->output;
    }
}
