<?php

declare(strict_types=1);

namespace MulAgent\Message;

enum ContentType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
}
