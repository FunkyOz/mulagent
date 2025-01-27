<?php

use MulAgent\Tool\ToolFormatter;

it('should parse anonymous tool', function () {
    enum TestIntEnum: int
    {
        case TEST = 0;
    }

    enum TestStringEnum: string
    {
        case TEST = 'test';
    }

    enum TestEnum
    {
        case TEST;
    }

    class Test
    {
        public function __construct(
            public string $name,
            public int $integer,
            public bool $boolean,
            public float $float,
            public ?TestStringEnum $stringEnum,
        ) {
        }
    }

    $myClass = new class () {
        public function __construct(
            public readonly string $name = 'test',
            public readonly string $description = 'Tool description',
        ) {
        }

        /**
         * @param  Test  $test The test description
         * @param  string  $myString The myString work as
         *                           a valid string type with description
         * @param  TestEnum  $myEnum
         * @param  TestIntEnum|null  $myIntEnum
         * @return void
         */
        public function __invoke(
            Test $test,
            string $myString,
            TestEnum $myEnum,
            ?TestIntEnum $myIntEnum,
        ) {
        }
    };
    $schema = ToolFormatter::formatToolAsJsonSchema($myClass);

    $expected = json_decode(file_get_contents(__DIR__.'/datasource/json_schema.json'), true);

    expect($schema)->toMatchArray($expected);
});

it('should format json schema name', function (string $name, string $expected) {
    expect(ToolFormatter::formatJsonSchemaName($name))->toBe($expected);
})->with([
    ['test', 'test'],
    ['testAgent', 'test_agent'],
    ['TestAgent', 'test_agent'],
    ['Test_Agent', 'test_agent'],
    ['anotherTest_Agent', 'another_test_agent'],
    ['123anotherTest_Agent', '123another_test_agent'],
    ['Test-agent', 'test_agent'],
]);
