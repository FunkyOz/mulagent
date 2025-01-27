<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MulAgent\Agent\Agent;
use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\MulAgent;

// This example demonstrates a basic conversation with a single agent
// without any tools. The agent will use its base capabilities to
// respond to user queries.

// Step 1: Configure OpenAI
// You can set these values through environment variables or directly
$config = OpenAIConfig::create([
    'model' => 'gpt-4',                                     // The model to use
    'temperature' => 1,                                     // Controls randomness (0-2)
    'api_key' => getenv('OPENAI_API_KEY'),                 // Your OpenAI API key
    'organization' => getenv('OPENAI_ORGANIZATION') ?: null // Optional: Your organization ID
]);

// Step 2: Create the LLM instance
// This handles the communication with OpenAI's API
$llm = new OpenAILLM($config);

// Step 3: Create an agent
// The agent is configured with a name and instructions that define its behavior
$agent = new Agent(
    name: 'Friendly Assistant',
    llm: $llm,
    instruction: <<<TEXT
You are a friendly and helpful assistant.
You should be concise but informative in your responses.
Always maintain a positive and professional tone.
TEXT
);

// Step 4: Initialize MulAgent
// MulAgent manages the conversation flow and agent interactions
$mulAgent = new MulAgent($agent);

// Step 5: Create a conversation
// Let's simulate a simple Q&A interaction
$conversation = [
    Message::user("Hi! Can you tell me about yourself?"),
    Message::user("What's the weather like today?"),
    Message::user("Thank you for your help!")
];

// Step 6: Run the conversation
// Process each message and get responses
foreach ($conversation as $message) {
    echo "\nUser: " . $message->content . "\n";
    
    // Run the agent and get its response
    $response = $mulAgent->run([$message]);
    
    // Print the agent's response
    echo "Assistant: " . $response . "\n";
} 