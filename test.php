<?php

use MulAgent\Agent\Agent;
use MulAgent\Agent\AgentResult;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;
use MulAgent\Tool\Property;
use MulAgent\Tool\Tool;
use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolDefinition;
use MulAgent\Tool\ToolOutput;

include __DIR__.'/vendor/autoload.php';

$addTool = new class () implements Tool {
    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition(
            name: 'add',
            description: 'Makes mathematical additions',
            properties: [
                new Property(
                    type: 'number',
                    name: 'first',
                ),
                new Property(
                    type: 'number',
                    name: 'second',
                )
            ],
            required: ['first', 'second'],
        );
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        if (!isset($toolCall->arguments['first']) || !isset($toolCall->arguments['second'])) {
            throw new InvalidArgumentException('Add tool expect exactly two arguments');
        }
        $first = $toolCall->arguments['first'];
        $second = $toolCall->arguments['second'];

        return new ToolOutput(
            content: (string)($first + $second),
            toolName: $toolCall->name,
        );
    }
};

$subTool = new class () implements Tool {
    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition(
            name: 'sub',
            description: 'Makes mathematical subtractions',
            properties: [
                new Property(
                    type: 'number',
                    name: 'first',
                ),
                new Property(
                    type: 'number',
                    name: 'second',
                )
            ],
            required: ['first', 'second'],
        );
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        if (!isset($toolCall->arguments['first']) || !isset($toolCall->arguments['second'])) {
            throw new InvalidArgumentException('Sub tool expect exactly two arguments');
        }
        $first = $toolCall->arguments['first'];
        $second = $toolCall->arguments['second'];

        return new ToolOutput(
            content: (string)($first - $second),
            toolName: $toolCall->name,
        );
    }
};

$mulTool = new class () implements Tool {
    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition(
            name: 'mul',
            description: 'Makes mathematical multiplications',
            properties: [
                new Property(
                    type: 'number',
                    name: 'first',
                ),
                new Property(
                    type: 'number',
                    name: 'second',
                )
            ],
            required: ['first', 'second'],
        );
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        if (!isset($toolCall->arguments['first']) || !isset($toolCall->arguments['second'])) {
            throw new InvalidArgumentException('Mul tool expect exactly two arguments');
        }
        $first = $toolCall->arguments['first'];
        $second = $toolCall->arguments['second'];

        return new ToolOutput(
            content: (string)($first * $second),
            toolName: $toolCall->name,
        );
    }
};

$divTool = new class () implements Tool {
    public function getDefinition(): ToolDefinition
    {
        return new ToolDefinition(
            name: 'div',
            description: 'Makes mathematical divisions',
            properties: [
                new Property(
                    type: 'number',
                    name: 'first',
                ),
                new Property(
                    type: 'number',
                    name: 'second',
                )
            ],
            required: ['first', 'second'],
        );
    }

    public function run(ToolCall $toolCall): ToolOutput
    {
        if (!isset($toolCall->arguments['first']) || !isset($toolCall->arguments['second'])) {
            throw new InvalidArgumentException('Div tool expect exactly two arguments');
        }
        $first = $toolCall->arguments['first'];
        $second = $toolCall->arguments['second'];
        if ($second === 0) {
            throw new InvalidArgumentException('Unable to divide by 0');
        }

        return new ToolOutput(
            content: (string)($first / $second),
            toolName: $toolCall->name,
        );
    }
};

$config = OpenAIConfig::create();
$llm = new OpenAILLM($config);

$agent = new Agent(
    name: 'Mathematician',
    llm: $llm,
    instruction: 'You are an mathematical expert.',
    tools: [$addTool, $subTool, $mulTool, $divTool],
);

$runner = new MulAgent($agent);

$firstPrompt = 'Hello, how can I help you?'.PHP_EOL.PHP_EOL;
$history = [];
while (true) {
    $line = readline($firstPrompt);
    echo PHP_EOL;
    readline_add_history($line);
    $history[] = Message::user($line);
    $response = $runner->run($history);
    $history = array_merge($history, array_map(fn (AgentResult $result) => $result->message, $response->results));
    echo $response.PHP_EOL.PHP_EOL;
    $firstPrompt = null;
}
