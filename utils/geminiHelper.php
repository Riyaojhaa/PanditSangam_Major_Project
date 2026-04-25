<?php

// Load .env file
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

function callGemini($imageUrl) {

    $apiKey = getenv("GEMINI_API_KEY");

    if (empty($apiKey)) {
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode" => 500,
                "responseData" => ["result" => null],
                "responseMessage" => "GEMINI_API_KEY not set in .env",
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "GEMINI_API_KEY not set in .env"
        ]);
        return null;
    }

    $prompt = 'You are a professional palm reader. Analyze the palm image carefully and return ONLY valid JSON with no extra text, no markdown, no code fences, no newlines inside values. Use this exact format:

{"summary":"Overall reading summary here","life_line":"Detailed life line reading here","heart_line":"Detailed heart line reading here","head_line":"Detailed head line reading here","fate_line":"Detailed fate line reading here","sun_line":"Detailed sun line reading here"}';

    $imageData = base64_encode(file_get_contents($imageUrl));

    $data = [
        "contents" => [[
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => "image/jpeg",
                        "data" => $imageData
                    ]
                ]
            ]
        ]],
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 2048,
            "responseMimeType" => "application/json"
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode" => 500,
                "responseData" => ["result" => null],
                "responseMessage" => "cURL error: " . $curlError,
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "cURL error: " . $curlError
        ]);
        return null;
    }

    $res = json_decode($response, true);

    if (!$res) {
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => ["result" => null],
                "responseMessage" => "Invalid response from Gemini API",
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid response from Gemini API"
        ]);
        return null;
    }

    if (isset($res['error'])) {
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => ["result" => null],
                "responseMessage" => $res['error']['message'] ?? "Gemini API error",
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => $res['error']['message'] ?? "Gemini API error"
        ]);
        return null;
    }

    $text = $res['candidates'][0]['content']['parts'][0]['text'] ?? "";

    if (empty($text)) {
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => ["result" => null],
                "responseMessage" => "Empty response from Gemini",
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Empty response from Gemini"
        ]);
        return null;
    }

    return formatPalmResult($text);
}

function formatPalmResult($text) {

    // Remove markdown code fences
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/i', '', $text);
    $text = trim($text);

    // Try direct JSON parse
    $parsed = json_decode($text, true);

    // If failed, extract JSON using regex
    if (!$parsed) {
        preg_match('/\{.*\}/s', $text, $matches);
        $jsonString = $matches[0] ?? "";
        $parsed = json_decode($jsonString, true);
    }

    // If summary itself contains a JSON string (nested), parse it
    if ($parsed && isset($parsed['summary']) && is_string($parsed['summary'])) {
        $nestedCheck = json_decode($parsed['summary'], true);
        if ($nestedCheck && isset($nestedCheck['summary'])) {
            $parsed = $nestedCheck;
        }
    }

    if (!$parsed) {
        return [
            "summary" => $text,
            "predictions" => []
        ];
    }

    return [
        "summary" => $parsed['summary'] ?? "Not available",
        "predictions" => [
            [
                "category" => "Life Line",
                "emoji" => "✋",
                "headline" => "Life Energy",
                "detail" => $parsed['life_line'] ?? "Not available",
                "score" => rand(70, 90) / 100
            ],
            [
                "category" => "Heart Line",
                "emoji" => "❤️",
                "headline" => "Love Nature",
                "detail" => $parsed['heart_line'] ?? "Not available",
                "score" => rand(60, 85) / 100
            ],
            [
                "category" => "Head Line",
                "emoji" => "🧠",
                "headline" => "Thinking Style",
                "detail" => $parsed['head_line'] ?? "Not available",
                "score" => rand(70, 90) / 100
            ],
            [
                "category" => "Fate Line",
                "emoji" => "🌟",
                "headline" => "Career Path",
                "detail" => $parsed['fate_line'] ?? "Not available",
                "score" => rand(60, 80) / 100
            ],
            [
                "category" => "Sun Line",
                "emoji" => "☀️",
                "headline" => "Success & Fame",
                "detail" => $parsed['sun_line'] ?? "Not available",
                "score" => rand(60, 75) / 100
            ]
        ]
    ];
}