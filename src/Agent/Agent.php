<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use MulAgent\LLM\LLM;
use MulAgent\Tool\Tool;

final class Agent
{
    /**
     * @param  non-empty-string  $name
     * @param  LLM  $llm
     * @param  string|null  $instruction
     * @param  array<Tool>  $tools
     */
    public function __construct(
        readonly string $name,
        readonly LLM $llm,
        readonly ?string $instruction = null,
        public array $tools = [],
    ) {
    }
}
