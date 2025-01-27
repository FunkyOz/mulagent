<?php

declare(strict_types=1);

namespace MulAgent\Agent;

use MulAgent\Message\Message;
use Stringable;

final class AgentResponse implements Stringable
{
    /**
     * @param  array<Message>  $messages
     * @param  Agent  $activeAgent
     */
    public function __construct(
        readonly array $messages,
        readonly Agent $activeAgent
    ) {
    }

    public function toString(): string
    {
        $messages = $this->messages;
        if (count($messages) === 0) {
            return '';
        }
        $message = end($messages);
        $content = $message->content;
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
