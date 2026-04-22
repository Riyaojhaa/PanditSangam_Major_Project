<?php

function callGemini($imageUrl){

    $apiKey = "AIzaSyDEYnlc9JNrBp1ko9eAsBwcT-3en0wFt8M";
    // $apiKey = getenv("GEMINI_API_KEY");

$prompt = "Analyze this palm image and describe:

1. Overall summary (2 lines)
2. Life Line
3. Heart Line
4. Head Line
5. Fate Line
6. Sun Line

Keep explanation simple and human-friendly.";
    // ⚠️ URL → base64
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
        ]]
    ];

    $ch = curl_init();

   curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $res = json_decode($response, true);
    // echo "<pre>";
    // print_r($res);
    // exit;
    if(!$res){
         echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => ["result" => null],
                "responseMessage" => "Palm reading AI service failed",
                "responseFrom" => "callGemini"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Palm reading AI service failed"
        ]);
        return;
    }

    // return $res['candidates'][0]['content']['parts'][0]['text'] ?? "No result";
    $text = $res['candidates'][0]['content']['parts'][0]['text'] ?? "";

    return formatPalmResult($text);
    
    print_r($data);
exit;
}

function formatPalmResult($text) {

    return [
        "summary" => extractSection($text, "Overall Impression"),

        "predictions" => [
            [
                "category" => "Life Line",
                "emoji" => "✋",
                "headline" => "Life Energy",
                "detail" => extractSection($text, "Life Line", 300),
                "score" => rand(70, 90) / 100
            ],
            [
                "category" => "Heart Line",
                "emoji" => "❤️",
                "headline" => "Love Nature",
                "detail" => extractSection($text, "Heart Line", 300),
                "score" => rand(60, 85) / 100
            ],
            [
                "category" => "Head Line",
                "emoji" => "🧠",
                "headline" => "Thinking Style",
                "detail" => extractSection($text, "Head Line", 300),
                "score" => rand(70, 90) / 100
            ],
            [
                "category" => "Fate Line",
                "emoji" => "🌟",
                "headline" => "Career Path",
                "detail" => extractSection($text, "Fate Line", 300),
                "score" => rand(60, 80) / 100
            ],
            [
                "category" => "Sun Line",
                "emoji" => "☀️",
                "headline" => "Success & Fame",
                "detail" => extractSection($text, "Sun Line", 300),
                "score" => rand(60, 75) / 100
            ]
        ]
    ];
}

function extractSection($text, $section) {

    $pattern = "/\\*\\*".$section.".*?\\*\\*(.*?)(?=\\n\\n|\\*\\*|$)/s";

    if(preg_match($pattern, $text, $matches)){
        return trim($matches[1]);
    }

    return "Not clearly visible, but indicates balanced traits.";
}