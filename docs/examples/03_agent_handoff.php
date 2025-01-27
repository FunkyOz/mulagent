<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;
use MulAgent\Tool\AgentTool;

// This example demonstrates how to create a system of specialized agents
// that can hand off conversations to each other based on the topic.

// First, let's create some math tools for our math expert
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

$derivativeTool = new class () {
    public string $name = 'derivative';

    public function __invoke(string $expression): string
    {
        // In a real implementation, you would implement derivative calculation
        return "Derivative of $expression calculated";
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

// Create specialized agents

// 1. Math Expert Agent
$mathAgent = new Agent(
    name: 'Math Expert',
    llm: $llm,
    instruction: <<<TEXT
You are a mathematics expert specializing in algebra, calculus, and statistics.
Focus on providing detailed mathematical explanations and solutions.
If a question is not related to mathematics, suggest handing off to literature expert.
TEXT,
    tools: [$addTool, $multiplyTool, $derivativeTool]
);

// 2. Literature Expert Agent (can hand off to math expert for calculations)
$literatureAgent = new Agent(
    name: 'Literature Expert',
    llm: $llm,
    instruction: <<<TEXT
You are a literature expert specializing in classical and modern literature.
Provide insights about books, authors, literary analysis, and writing techniques.
If a question is about mathematics, hand off to the math expert.
If a question is about science, hand off to the science expert.
TEXT,
    tools: [new AgentTool($mathAgent)]
);

// 3. Science Expert Agent (can hand off to math expert for calculations)
$scienceAgent = new Agent(
    name: 'Science Expert',
    llm: $llm,
    instruction: <<<TEXT
You are a science expert specializing in physics, chemistry, and biology.
Explain scientific concepts and phenomena.
For complex mathematical calculations, hand off to the math expert.
For questions about literature or writing, hand off to the literature expert.
TEXT,
    tools: [$mathAgent, $literatureAgent] // You can also pass agent directly
);

$mathAgent->addTools([$literatureAgent]);

// Initialize MulAgent with the science expert as the starting point
$mulAgent = new MulAgent($scienceAgent);

// Example conversation demonstrating handoffs
$conversation = [
    Message::user("Can you explain how photosynthesis works?"),
    Message::user("What's the derivative of x^2 + 3x + 2?"),
    Message::user("Who wrote 'Pride and Prejudice' and what themes does it explore?"),
    Message::user("If a plant produces 6 grams of glucose per hour through photosynthesis, how much will it produce in 24 hours?")
];

// Run the conversation
foreach ($conversation as $message) {
    echo "\nUser: " . $message->content . "\n";
    $response = $mulAgent->run([$message]);
    echo "Assistant: " . $response . "\n";
}
