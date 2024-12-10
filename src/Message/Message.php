<?php

declare(strict_types=1);

namespace MulAgent\Message;

class Message
{
    public function __construct(
        readonly string $content,
        readonly MessageRole $role,
        readonly ?string $toolId = null,
    ) {
    }

    public static function user(string $content): Message
    {
        return new self($content, MessageRole::USER);
    }

    public static function assistant(string $content): Message
    {
        return new self($content, MessageRole::ASSISTANT);
    }

    public static function tool(string $content, string $toolId): Message
    {
        return new self($content, MessageRole::TOOL, $toolId);
    }

    public static function system(string $content): Message
    {
        return new self($content, MessageRole::SYSTEM);
    }

    public function isTool(): bool
    {
        return MessageRole::TOOL === $this->role;
    }

    public function isSystem(): bool
    {
        return MessageRole::SYSTEM === $this->role;
    }

    public function isAssistant(): bool
    {
        return MessageRole::ASSISTANT === $this->role;
    }

    public function isUser(): bool
    {
        return MessageRole::USER === $this->role;
    }
}
