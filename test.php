<?php

use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;

include __DIR__.'/vendor/autoload.php';

$addTool = new class () {
    public string $name = 'add';

    public function __invoke(float $first, float $second): string
    {
        return (string)($first + $second);
    }
};

$subTool = new class () {
    public string $name = 'sub';

    public function __invoke(float $first, float $second): string
    {
        return (string)($first - $second);
    }
};

$mulTool = new class () {
    public string $name = 'mul';

    public function __invoke(float $first, float $second): string
    {
        return (string)($first * $second);
    }
};

$divTool = new class () {
    public string $name = 'div';

    public function __invoke(float $first, float $second): string
    {
        if (.0 === $second) {
            return 'Unable to divide by 0';
        }

        return (string)($first / $second);
    }
};

$defaultConfig = [];
if ($baseUrl = getenv('BASE_URL')) {
    $defaultConfig['base_url'] = $baseUrl;
}
if ($apiKey = getenv('OPENAI_API_KEY')) {
    $defaultConfig['api_key'] = $apiKey;
}
if ($heliconeApiKey = getenv('HELICONE_API_KEY')) {
    $defaultConfig['headers'] = [
        'Helicone-Auth' => 'Bearer '.$heliconeApiKey
    ];
}

$config = OpenAIConfig::create($defaultConfig);
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
    $history = array_merge($history, $response->messages);
    echo $response.PHP_EOL.PHP_EOL;
    $firstPrompt = null;
}
