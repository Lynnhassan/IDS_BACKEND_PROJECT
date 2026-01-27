<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class AiSummarizeController extends Controller
{
    public function getSummary($courseId)
    {
        // 1. Find the course
        $course = Course::findOrFail($courseId);

        // 2. Return cached summary if it exists
        if ($course->pdf_summary) {
            return response()->json([
                'success' => true,
                'summary' => $course->pdf_summary
            ]);
        }

        // 3. Check if PDF path exists
        if (!$course->pdf) {
            return response()->json(['success' => false, 'message' => 'No PDF attached.'], 404);
        }

        try {
            // 4. Extract text from PDF
            $parser = new Parser();
            $path = storage_path('app/public/' . $course->pdf);

            if (!file_exists($path)) {
                return response()->json(['success' => false, 'message' => 'File not found.'], 404);
            }

            $pdfContent = $parser->parseFile($path);
            $text = trim($pdfContent->getText());

            if (empty($text)) {
                return response()->json(['success' => false, 'message' => 'PDF is empty or scanned image.']);
            }

            // Limit text (llama-3.3-70b has a large context, but 15k is safe for a summary)
            $text = substr($text, 0, 15000);

            // 5. Call Groq API
            $apiKey = env('GROQ_API_KEY');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an academic assistant. Summarize the text into clear, professional bullet points.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Summarize this: \n\n" . $text
                    ],
                ],
                'temperature' => 0.5,
            ]);

            $result = $response->json();

            // 6. Handle Response
            if (isset($result['choices'][0]['message']['content'])) {
                $summary = $result['choices'][0]['message']['content'];

                // 7. Save and Return
                $course->update(['pdf_summary' => $summary]);

                return response()->json([
                    'success' => true,
                    'summary' => $summary
                ]);
            }

            $errorMsg = $result['error']['message'] ?? 'Groq API failed.';
            return response()->json(['success' => false, 'message' => 'Groq Error: ' . $errorMsg]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    } // End getSummary
} // End Class
