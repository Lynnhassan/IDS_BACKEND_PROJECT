<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;

class QuizGenerator
{
    public function generateFromPdf(string $pdfPath, int $quantity = 5)
    {
        // 1. Extract Text
        $parser = new Parser();
        try {
            // Adjust path based on your storage config
            $pdf = $parser->parseFile(storage_path('app/public/' . $pdfPath));
            $text = $pdf->getText();

            // Limit text to avoid token limits (approx 15k chars ~ 4k tokens)
            // For production, you might need a chunking strategy
            $text = substr($text, 0, 15000);
        } catch (Exception $e) {
            throw new Exception("Could not read PDF file.");
        }

        // 2. Prepare the AI Prompt
        $prompt = "
            Analyze the following course text and generate {$quantity} quiz questions.

            Output format must be strict JSON:
            {
                \"questions\": [
                    {
                        \"questionText\": \"The question string\",
                        \"questionType\": \"single\",
                        \"answers\": [
                            {\"answerText\": \"Option A\", \"isCorrect\": true},
                            {\"answerText\": \"Option B\", \"isCorrect\": false},
                            {\"answerText\": \"Option C\", \"isCorrect\": false},
                            {\"answerText\": \"Option D\", \"isCorrect\": false}
                        ]
                    }
                ]
            }

            Course Text:
            {$text}
        ";

        // 3. Call AI
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini', // or 'gpt-3.5-turbo' for lower cost
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful education assistant. Always return valid JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'], // Enforces JSON structure
        ]);

        return json_decode($result->choices[0]->message->content, true);
    }
}
