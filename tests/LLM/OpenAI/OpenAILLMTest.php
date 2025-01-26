<?php

use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\Message;
use MulAgent\Message\MessageRole;
use MulAgent\Message\TextContent;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

it('should create openai llm', function () {
    $config = OpenAIConfig::create([
        'client' => new ClientFake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'My test!',
                        ]
                    ]
                ]
            ])
        ])
    ]);
    $llm = new OpenAILLM($config);
    $result = $llm->chat();
    expect($result->message)
        ->content->{0}->text->toBe('My test!')
        ->role->toBe(MessageRole::ASSISTANT);
});

it('should expect text content', function () {
    $config = createFakeOpenAIConfig([
        createFakeOpenAIChatResponse([
            'content' => 'Test response',
        ]),
    ]);
    $llm = new OpenAILLM($config);
    $result = $llm->chat([
        new Message(
            content: new TextContent('Test response'),
            role: MessageRole::USER
        )
    ]);
    expect($result->message)
        ->content->{0}->text->toBe('Test response')
        ->role->toBe(MessageRole::ASSISTANT);
});
