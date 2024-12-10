<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use MulAgent\LLM\LLMInterface;
use MulAgent\Tool\ToolInterface;

final class Agent
{
    /**
     * @param  non-empty-string  $name
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
