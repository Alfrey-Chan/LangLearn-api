<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService 
{
    private $apiKey;
    private $url;
    private $promptId;
    private $promptVersion;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->url = config('services.openai.url');
        $this->promptId = config('services.openai.prompt_id');
        $this->promptVersion = config('services.openai.prompt_version');
    }

    public function submitQuizAnswers($quiz)
    {   
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->url, [
                'prompt' => [
                    'id'      => $this->promptId,
                    'version' => $this->promptVersion,
                    'variables' => [
                        'questions' => json_encode($quiz, JSON_UNESCAPED_UNICODE)
                    ]
                ]
            ]);

            $result = $response->json()['output'][0][
                'content'][0]['text'];
            Log::info('response' . $result);

            if ($response->successful()) {
                return json_decode($result, true);
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            throw $e;
        }
    }
}