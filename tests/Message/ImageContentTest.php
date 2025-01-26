<?php

use MulAgent\Message\ImageContent;

it('should throw invalid argument exception', function () {
    new ImageContent('foo');
})->throws(
    InvalidArgumentException::class,
    'Invalid url argument, expected an URL, got "foo"'
);
