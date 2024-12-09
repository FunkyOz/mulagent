<?php

use Mulagent\Utility\Utility;

it('should splice array', function (array $arr, int $from, ?int $to, array $expected) {
    $initCount = count($arr);
    $result = Utility::arraySplice($arr, $from, $to);
    expect($initCount)->toBe(count($arr))
        ->and($result)->toBe($expected);
})->with([
    'empty array from 0 to 0 should return an empty array' => [[], 0, 0, []],
    'empty array from 0 to 1 should return an empty array' => [[], 0, 1, []],
    'empty array from 1 to n should return an empty array' => [[], 1, 4, []],
    'one-item array from 0 to 0 should return an empty array' => [['test'], 0, 0, []],
    'one-item array from 0 should return the same array' => [['test'], 0, null, ['test']],
    'n-item array from 0 to 1 should return a one-item array' => [['test', 'test2', 'test3'], 0, 1, ['test']],
    'n-item array from 0 should return the same array' => [
        ['test', 'test2', 'test3'],
        0,
        null,
        ['test', 'test2', 'test3']
    ],
    'n-item array from 2 to 3 should return one-item array' => [
        ['test', 'test2', 'test3'],
        2,
        3,
        ['test3']
    ],
]);

it('should clone array', function (array $arr) {
    $result = Utility::arrayClone($arr);
    assertArrayEqual($result, $arr);
})->with([
    'empty array' => [[]],
    'one-dimension array' => [['test']],
    'multi-dimension array' => [[['test']]],
    'map array' => [['test' => 'test']],
    'multi-dimension map array' => [[['test' => 'test']]],
    'general array' => [[['test' => 'test'], 'test', new stdClass(), 'object' => new stdClass()]],
]);
