<?php

declare(strict_types=1);

namespace MulAgent\Message;

enum MessageRole: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
    case TOOL = 'tool';
}
