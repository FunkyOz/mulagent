<p style="text-align: center;">
    <img src="https://raw.githubusercontent.com/FunkyOz/mulagent/main/docs/mulagent-screen.jpg" height="300" alt="MulAgent">
    <p style="text-align: center;">
        <a href="https://github.com/FunkyOz/mulagent/actions">
            <img alt="GitHub Tests (main)" src="https://img.shields.io/github/actions/workflow/status/funkyoz/mulagent/tests.yml?branch=main">
        </a>
        <a href="https://packagist.org/packages/FunkyOz/mulagent">
            <img alt="Downloads" src="https://img.shields.io/packagist/dt/funkyoz/mulagent">
        </a>
        <a href="https://packagist.org/packages/FunkyOz/mulagent">
            <img alt="Latest Version" src="https://img.shields.io/packagist/v/funkyoz/mulagent">
        </a>
        <a href="https://packagist.org/packages/FunkyOz/mulagent">
            <img alt="License" src="https://img.shields.io/packagist/l/funkyoz/mulagent">
        </a>
    </p>
</p>

------

This package provides a simple Multi-Agent implementation of an LLM application in PHP.
Inspired by [OpenAI Swarm](https://github.com/openai/swarm).
Support only OpenAI api

> **Requires [PHP 8.1+](https://php.net/releases/)**

### Installation

```bash
composer require FunkyOz/mulagent
```

### The orchestration

The orchestration of multiple agents involves the use of routines and handoffs.
Simplifying, a routine is a series of steps to follow to achieve a goal,
and a handoff is the transition from one agent to another, like a switchboard transferring a phone call.

Every agent has tools used to achieve the routine's goal and can make a handoff, transfer the conversation to other
agents.

You can find more details in the [Cookbook made by OpenAI](https://cookbook.openai.com/examples/orchestrating_agents)

### Usage

**Simple agent without tools**

```php
use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;

$config = OpenAIConfig::create([
    'model' => 'gpt-4o',                                     // The model to use.
    'temperature' => 1,                                      // The model temperature.
    'api_key' => getenv('OPENAI_API_KEY'),                   // Your OpenAI api key.
    'organization' => getenv('OPENAI_ORGANIZATION') ?: null, // Your OpenAI organization.
    'base_url' => getenv('OPENAI_BASE_URL') ?: null,         // If you need to use another base url.
    'headers' => [],                                         // Additional headers passed to the client.
    'client' => null                                         // If you want to pass a custom \OpenAI\Client other properties will not be used.
]);
$llm = new OpenAILLM($config);
$agent = new Agent(
    name: 'My agent',                                        // The name of the agent. 
    llm: $llm,                                               // The llm to use.
    instruction: 'You are an...',                            // Custom instruction passed as a system message to the api.
);
$mulAgent = new MulAgent($agent);

$messages = [Message::user('Hi!')];
$response = $mulAgent->run(
    messages: $messages,                                     // The conversation list: if there is a system message inside the list, it will be overridden by the agent instruction. 
    maxTurns: PHP_INT_MAX,                                   // The maximum llm calls iterations, default to php const PHP_INT_MAX (https://www.php.net/manual/en/reserved.constants.php#constant.php-int-max).
    executeTools: true,                                      // A boolean value using to enable or disable tool executions.
);

$content = $response->getContent();                          // The response as string after all routines and hadoffs was completed.
$response->activeAgent;                                      // The last agent responded.
$response->results;                                          // A list of result object, composed by a message and an eventual tool output.
$response->results[0]->message;                              // The message of a result.
$response->results[0]->toolOutput;                           // If there were any tool calls this variable contain the output used by the llm.
```

**Agent transfers the conversation to another**

```php
use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;
use MulAgent\Tool\AgentTool;

$firstAgent = new Agent(
    name: 'My agent', 
    llm: $llm,
    instruction: <<<TEXT
If the user greeting you,
transfer the conversation to the other agent
TEXT
);
$otherAgent = new Agent(
    name: 'My agent', 
    llm: $llm,
);
$activeAgent->tools = [
    new AgentTool(
        agent: $otherAgent,                           // The agent to use as tool. 
        toolName: 'other_agent',                      // The tool's name. if omitted, the agent name will be used.
        toolDescription: 'Description of other agent' // The tool's description used to help the llm.
    )
]; 

$mulAgent = new MulAgent($activeAgent);

$messages = [Message::user('Hi!')];
$response = $mulAgent->run(
    messages: $messages, 
    maxTurns: PHP_INT_MAX,
    executeTools: true,
);
```

**Using your own tool**

```php
use MulAgent\Tool\ToolCall;
use MulAgent\Tool\ToolDefinition;
use MulAgent\Tool\ToolInterface;
use MulAgent\Tool\ToolOutput;
use MulAgent\Tool\Property;

class MyTool implements ToolInterface
{
    public function getDefinition(): ToolDefinition
    {
        // You must define the tool in a JSON schema format.    
        return new ToolDefinition(
            name: 'my_tool',
            description: 'A description to help llm about this tool',
            properties: [
                new Property(
                    type: 'string',
                    name: 'query',
                )
            ]
        );
    }
    
    public function run(ToolCall $toolCall): ToolOutput
    {
        // Execute you tool here
        // $toolCall->id is the id of the tool call created by openai.
        // $toolCall->name is the name of the tool
        // $toolCall->argument is an array of argument from your tool definition
        if (empty($toolCall->arguments['query'])) {
            return new ToolOutput('', $toolCall->name);
        }
        $documents = $myRetriever->retrieve($toolCall->arguments['query']);
        
        $docAsString = array_reduce(
            $documents, 
            fn(string $carry, Document $doc) => $carry.PHP_EOL.PHP_EOL.$doc->content, 
            ''
        );
        
        return new ToolOutput(
            content: $docAsString, 
            toolName: $toolCall->name,
            output: $documents
        );
    }
}

$agent = new Agent(
    name: 'My agent', 
    llm: $llm,
    tools: [new MyTool()]
);

$mulAgent = new MulAgent($agent);

$messages = [Message::user('Hi!')];
$response = $mulAgent->run(
    messages: $messages, 
    maxTurns: PHP_INT_MAX,
    executeTools: true,
);
```

**MulAgent** was created by **[Lorenzo Dessimoni](https://github.com/FunkyOz)** under the 
**[MIT license](https://opensource.org/licenses/MIT)**.
