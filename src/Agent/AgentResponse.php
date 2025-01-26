<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use Stringable;

final class AgentResponse implements Stringable
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

    public function toString(): string
    {
        $results = $this->results;
        if (count($results) === 0) {
            return '';
        }
        $result = end($results);
        $content = $result->message->content;
        if (count($content) === 0) {
            return '';
        }
        $content = end($content);
        return $content->getValue();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
