<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use InvalidArgumentException;
use MulAgent\LLM\LLM;
use MulAgent\Tool\AgentTool;

final class Agent
{
    /**
     * @var array<callable-object>
     */
    private array $tools = [];

    /**
     * @param  non-empty-string  $name
     * @param  LLM  $llm
     * @param  string|null  $instruction
     * @param  array<callable-object|Agent>  $tools  Objects that must implement __invoke() method
     */
    public function __construct(
        readonly string $name,
        readonly LLM $llm,
        readonly ?string $instruction = null,
        array $tools = [],
    ) {
        $this->addTools($tools);
    }

    /**
     * @param  array<callable-object|Agent>  $tools
     * @return void
     */
    public function addTools(array $tools): void
    {
        $tools = array_map(self::ensureTool(...), $tools);
        $this->tools = array_merge($this->tools, $tools);
    }

    /**
     * @return array<callable-object>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * @param  callable-object|Agent  $tool
     * @return callable-object
     */
    private static function ensureTool(mixed $tool)
    {
        if ($tool instanceof Agent) {
            $tool = new AgentTool($tool);
        }
        if (is_object($tool) && !method_exists($tool, '__invoke')) {
            throw new InvalidArgumentException('Tool must be a valid Functor, implement __invoke()');
        }
        return $tool;
    }
}
