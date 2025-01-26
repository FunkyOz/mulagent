<?php

use MulAgent\Message\ImageContent;
use MulAgent\Message\Message;
use MulAgent\Message\MessageRole;
use MulAgent\Message\TextContent;

it('should create new message', function (string|TextContent|ImageContent|array $content, $expected) {
    $message = new Message($content, MessageRole::USER);

    expect($message->toString())->toBe($expected);
})->with([
    'TextContent' => [
        new TextContent('Test'),
        'user: Test'
    ],
    'ImageContent' => [
        new ImageContent('http://test'),
        'user: http://test'
    ],
    'string become text' => [
        'test',
        'user: test'
    ],
    'string become image_url' => [
        'http://test',
        'user: http://test'
    ],
    'array{TextContent}' => [
        [
            new TextContent('Test')
        ],
        'user: Test'
    ],
    'array{ImageContent}' => [
        [
            new ImageContent('http://test')
        ],
        'user: http://test'
    ],
    'array{TextContent, ImageContent}' => [
        [
            new TextContent('Test'),
            new ImageContent('http://test')
        ],
        'user: Test'.PHP_EOL.'http://test'
    ],
    'array{TextContent, TextContent, ImageContent}' => [
        [
            new TextContent('Test'),
            new TextContent('Test'),
            new ImageContent('http://test')
        ],
        'user: Test'.PHP_EOL.'Test'.PHP_EOL.'http://test',
    ],
    'array{ImageContent, TextContent}' => [
        [
            new ImageContent('http://test'),
            new TextContent('Test')
        ],
        'user: http://test'.PHP_EOL.'Test'
    ],
]);
