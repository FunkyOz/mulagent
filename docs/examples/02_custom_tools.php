<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;

// This example demonstrates how to create and use custom tools with an agent
// using PHP functors (classes that implement __invoke).

// First, let's create a simple weather tool
$weatherTool = new class () {
    public string $name = 'weather';
    
    private array $mockWeather = [
        'London' => ['temp' => 15, 'condition' => 'cloudy'],
        'Paris' => ['temp' => 18, 'condition' => 'sunny'],
        'New York' => ['temp' => 22, 'condition' => 'rainy']
    ];

    public function __invoke(string $city): string
    {
        if (!isset($this->mockWeather[$city])) {
            return "Weather data not available for $city";
        }

        $weather = $this->mockWeather[$city];
        return "In $city it's {$weather['condition']} with {$weather['temp']}°C";
    }
};

// Now, let's create calculator tools
$addTool = new class () {
    public string $name = 'add';

    public function __invoke(float $first, float $second): string
    {
        return (string)($first + $second);
    }
};

$multiplyTool = new class () {
    public string $name = 'multiply';

    public function __invoke(float $first, float $second): string
    {
        return (string)($first * $second);
    }
};

$divideTool = new class () {
    public string $name = 'divide';

    public function __invoke(float $first, float $second): string
    {
        if (.0 === $second) {
            return 'Cannot divide by zero';
        }
        return (string)($first / $second);
    }
};

// Set up OpenAI configuration
$config = OpenAIConfig::create([
    'model' => 'gpt-4',
    'temperature' => 1,
    'api_key' => getenv('OPENAI_API_KEY')
]);

// Create LLM instance
$llm = new OpenAILLM($config);

// Create an agent with both weather and calculator tools
$agent = new Agent(
    name: 'Multi-Tool Assistant',
    llm: $llm,
    instruction: <<<TEXT
You are an assistant with access to weather information and calculation capabilities.
Use the weather tool when asked about weather in specific cities.
Use the calculator tools for mathematical operations.
TEXT,
    tools: [
        $weatherTool,
        $addTool,
        $multiplyTool,
        $divideTool
    ]
);

// Initialize MulAgent
$mulAgent = new MulAgent($agent);

// Example conversation using both tools
$conversation = [
    Message::user("What's the weather like in London?"),
    Message::user("Can you add 15 and 27?"),
    Message::user("What's the weather in Paris and can you multiply its temperature by 2?")
];

// Run the conversation
foreach ($conversation as $message) {
    echo "\nUser: " . $message->content . "\n";
    $response = $mulAgent->run([$message]);
    echo "Assistant: " . $response . "\n";
} 