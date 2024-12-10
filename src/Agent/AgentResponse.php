<?php

declare(strict_types=1);

namespace MulAgent\Agent;

class AgentResponse
{
    /**
     * @param  array<AgentResult>  $results
     * @param  Agent  $activeAgent
     */
    public function __construct(
        readonly array $results,
        readonly Agent $activeAgent
    ) {
    }

    public function getContent(): string
    {
        if (!isset($this->results[count($this->results) - 1])) {
            return '';
        }
        return $this->results[count($this->results) - 1]->message->content;
    }
}
