<?php

declare(strict_types=1);

namespace MulAgent\LLM;

use MulAgent\Message\Message;

interface LLM
{
    /**
     * @param  array<Message>  $messages
     * @param  array<callable-object>  $tools
     * @return LLMResult
     */
    public function chat(array $messages = [], array $tools = []): LLMResult;
}
