<?php

declare(strict_types=1);

namespace Mulagent\Agent;

class AgentResponse
{
    /**
     * @param  array<AgentResult>  $results
     * @param  Agent  $activeAgent
     */
    public function __construct(readonly array $results, readonly Agent $activeAgent)
    {
    }
}
