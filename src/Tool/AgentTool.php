<?php

declare(strict_types=1);

namespace MulAgent\Tool;

use MulAgent\Agent\Agent;

final class AgentTool
{
    public readonly string $name;
    public readonly string $description;


    public function __construct(
        private readonly Agent $agent,
        ?string $name = null,
        ?string $description = null
    ) {
        $name ??= $this->agent->name;
        $this->name = 'transfer_to_'.ToolFormatter::formatJsonSchemaName($name).'_agent';
        $this->description = $description ?? 'Transfer the conversation to agent '.$name;
    }

    public function __invoke(): Agent
    {
        return $this->agent;
    }
}
