<?php

declare(strict_types=1);

namespace MulAgent\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class ExceptionFactory
{
    private const LLM_BAD_REQUEST_EX_CODE = 1;
    private const INVALID_RESPONSE_EX_CODE = 2;
    private const TOOL_FORMAT_EX_CODE = 3;
    private const TOOL_EXECUTION_EX_CODE = 4;
    private const INVALID_IMAGE_EX_CODE = 4;

    /**
     * @param  string  $message
     * @param  array<mixed>  $parameters
     * @return LLMBadRequestException
     */
    public static function createLLMBadRequestException(string $message, array $parameters): LLMBadRequestException
    {
        return new LLMBadRequestException($parameters, $message, self::LLM_BAD_REQUEST_EX_CODE);
    }

    public static function createInvalidResponseException(string $message): InvalidResponseException
    {
        return new InvalidResponseException($message, self::INVALID_RESPONSE_EX_CODE);
    }

    public static function createToolFormatException(string $message): ToolFormatException
    {
        return new ToolFormatException($message, self::TOOL_FORMAT_EX_CODE);
    }

    public static function createToolExecutionException(string $message): ToolExecutionException
    {
        return new ToolExecutionException($message, self::TOOL_EXECUTION_EX_CODE);
    }

    public static function createAnthropicDisabledException(): InvalidArgumentException
    {
        return new InvalidArgumentException('Anthropic support disabled, run composer require mozex/anthropic-php');
    }

    public static function createInvalidImageException(string $message): InvalidImageException
    {
        return new InvalidImageException($message, self::INVALID_IMAGE_EX_CODE);
    }
}
