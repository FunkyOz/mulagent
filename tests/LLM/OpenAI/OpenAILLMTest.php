<?php

use MulAgent\LLM\OpenAI\OpenAIConfig;
use MulAgent\LLM\OpenAI\OpenAILLM;
use MulAgent\Message\MessageRole;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

it('should create llm correctly', function () {
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
        ->content->toBe('My test!')
        ->role->toBe(MessageRole::ASSISTANT);
});
