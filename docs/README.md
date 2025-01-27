# MulAgent Documentation

<p align="center">
  <img src="mulagent-screen.jpg" alt="MulAgent" height="300">
</p>

<p align="center">
  <a href="https://github.com/FunkyOz/mulagent/actions">
    <img alt="GitHub Tests" src="https://img.shields.io/github/actions/workflow/status/funkyoz/mulagent/tests.yml?branch=main">
  </a>
  <a href="https://packagist.org/packages/funkyoz/mulagent">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/funkyoz/mulagent">
  </a>
  <a href="https://packagist.org/packages/funkyoz/mulagent">
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/funkyoz/mulagent">
  </a>
  <a href="https://packagist.org/packages/funkyoz/mulagent">
    <img alt="License" src="https://img.shields.io/packagist/l/funkyoz/mulagent">
  </a>
</p>

MulAgent is a PHP package that provides a simple Multi-Agent implementation for LLM applications. It allows you to create and orchestrate multiple AI agents that can work together, each with their own specific tools and capabilities. The package currently supports OpenAI's API.

Inspired by [OpenAI Swarm](https://github.com/openai/swarm).

## Key Features

- Multi-agent orchestration with routines and handoffs
- Simple tool-based architecture using PHP functors
- Simple integration with OpenAI's API
- Support for PHP 8.1+
- Type-safe implementation

## Quick Examples

### 1. Basic Agent Without Tools

```php
use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;

// Configure OpenAI
$config = OpenAIConfig::create([
    'model' => 'gpt-4',
    'temperature' => 1,
    'api_key' => getenv('OPENAI_API_KEY'),
]);

// Create LLM instance
$llm = new OpenAILLM($config);

// Create an agent
$agent = new Agent(
    name: 'Assistant',
    llm: $llm,
    instruction: 'You are a helpful assistant.'
);

// Initialize MulAgent
$mulAgent = new MulAgent($agent);

// Run a conversation
$messages = [Message::user('What is the capital of France?')];
$response = $mulAgent->run($messages);
```

### 2. Agent with Custom Tools

```php
// Create calculator tools using PHP functors
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

// Create and use the agent with calculator tools
$agent = new Agent(
    name: 'Calculator',
    llm: $llm,
    instruction: 'You are a math assistant.',
    tools: [$addTool, $multiplyTool, $divideTool]
);

$mulAgent = new MulAgent($agent);
```

### 3. Agent with Handoff Capability

```php
use MulAgent\Tool\AgentTool;

// Create two specialized agents
$mathAgent = new Agent(
    name: 'Math Expert',
    llm: $llm,
    instruction: 'You are a mathematics expert.'
);

$scienceAgent = new Agent(
    name: 'Science Expert',
    llm: $llm,
    instruction: 'You are a science expert.',
    tools: [new AgentTool($mathAgent)] // Science agent can hand off to math agent
);

$mulAgent = new MulAgent($scienceAgent);
```

For more detailed examples and advanced usage, check out the [examples](./examples) directory. 
