<?php

declare(strict_types=1);

namespace MulAgent\Message;

final class TextContent extends Content
{
    public function __construct(readonly string $text)
    {
    }

    public function getType(): ContentType
    {
        return ContentType::TEXT;
    }

    public function getValue(): string
    {
        return $this->text;
    }
}
