<?php

declare(strict_types=1);

namespace MulAgent\Message;

use Stringable;

final class Message implements Stringable
{
    /**
     * @var array<Content>
     */
    public readonly array $content;

    /**
     * @param  string|Content|array<Content>  $content
     * @param  MessageRole  $role
     * @param  array<string, mixed>  $additionalArgs
     */
    public function __construct(
        string|array|Content $content,
        readonly MessageRole $role,
        readonly array $additionalArgs = [],
    ) {
        if (is_string($content)) {
            if (false !== filter_var($content, FILTER_VALIDATE_URL)) {
                $content = [new ImageContent($content)];
            } else {
                $content = [new TextContent($content)];
            }
        } elseif (!is_array($content)) {
            $content = [$content];
        }
        $this->content = $content;
    }

    public static function user(string $content): Message
    {
        return new self($content, MessageRole::USER);
    }

    /**
     * @param  string  $content
     * @param  array<string, mixed>  $additionalArgs
     * @return Message
     */
    public static function assistant(string $content, array $additionalArgs = []): Message
    {
        return new self($content, MessageRole::ASSISTANT, $additionalArgs);
    }

    /**
     * @param  string  $content
     * @param  array<string, mixed>  $additionalArgs
     * @return Message
     */
    public static function tool(string $content, array $additionalArgs = []): Message
    {
        return new self($content, MessageRole::TOOL, $additionalArgs);
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

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return sprintf(
            '%s: %s',
            $this->role->value,
            implode(
                PHP_EOL,
                array_map(fn (Content $content) => $content->getValue(), $this->content)
            )
        );
    }
}
