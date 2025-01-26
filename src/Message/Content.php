<?php

declare(strict_types=1);

namespace MulAgent\Message;

abstract class Content
{
    abstract public function getType(): ContentType;

    abstract public function getValue(): string;
}
