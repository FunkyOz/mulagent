<?php

declare(strict_types=1);

namespace MulAgent\Message;

use InvalidArgumentException;

final class ImageContent extends Content
{
    public function __construct(readonly string $url)
    {
        if (false === filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid url argument, expected an URL, got "'.$url.'"');
        }
    }

    public function getType(): ContentType
    {
        return ContentType::IMAGE;
    }

    public function getValue(): string
    {
        return $this->url;
    }
}
