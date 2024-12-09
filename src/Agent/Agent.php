<?php

declare(strict_types=1);

namespace Mulagent\Agent;

use Mulagent\LLM\LLMInterface;
use Mulagent\Tool\ToolInterface;

class Agent
{
    /**
     * @param  string  $name
     * @param  LLMInterface  $llm
     * @param  string|null  $instruction
     * @param  array<ToolInterface>  $tools
     */
    public function __construct(
        readonly string $name,
        readonly LLMInterface $llm,
        readonly ?string $instruction = null,
        public array $tools = [],
    ) {
    }
}
