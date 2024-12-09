<?php

declare(strict_types=1);


namespace Mulagent\Exceptions;

class ExceptionFactory
{
    private const AGENT_CASTING_EX_CODE = 1;
    private const ASSERTION_EX_CODE = 2;
    private const INVALID_RESPONSE_EX_CODE = 3;
    private const TOOL_FORMAT_EX_CODE = 3;

    public static function createAgentCastingException(string $message): AgentCastingException
    {
        return new AgentCastingException($message, self::AGENT_CASTING_EX_CODE);
    }

    public static function createAssertionException(string $message): AssertionException
    {
        return new AssertionException($message, self::ASSERTION_EX_CODE);
    }

    public static function createInvalidResponseException(string $message): InvalidResponseException
    {
        return new InvalidResponseException($message, self::INVALID_RESPONSE_EX_CODE);
    }

    public static function createToolFormatException(string $message): ToolFormatException
    {
        return new ToolFormatException($message, self::TOOL_FORMAT_EX_CODE);
    }
}
