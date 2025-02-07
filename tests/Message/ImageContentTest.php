<?php

use MulAgent\Message\ContentType;
use MulAgent\Message\ImageContent;

it('should create a valid image content', function () {
    $content = new ImageContent('https://example.com/image.jpg');
    expect($content->getValue())->toBe('https://example.com/image.jpg')
        ->and($content->getType())->toBe(ContentType::IMAGE);
});

it('should throw invalid argument exception', function () {
    new ImageContent('foo');
})->throws(
    InvalidArgumentException::class,
    'Invalid url argument, expected an URL, got "foo"'
);
