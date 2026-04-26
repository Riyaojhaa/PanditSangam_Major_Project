<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function getQuestions(){

    global $db;

    $headers = getallheaders();

    // ✅ Token check
    if (!isset($headers['Authorization'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Token missing",
                "responseFrom"    => "getQuestions"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "token missing"
        ]);
        exit;
    }

    // ✅ Extract token
    $token = str_replace("Bearer ", "", $headers['Authorization']);
    $decoded = verifyJWT($token);

    if (!$decoded) {
         http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Invalid token",
                "responseFrom"    => "getQuestions"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    // ✅ Get language from query param
    $language = $_GET['language'] ?? null;

    if (!$language) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Language is required (hi/en/kn)",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Language is required (hi/en/kn)"
        ]);
        exit;
    }

    // ✅ Validate language
    $allowedLangs = ['hi', 'en', 'kn'];
    if (!in_array($language, $allowedLangs)) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Invalid language",
                "responseFrom"    => "getQuestions"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Invalid language"
        ]);
        exit;
    }

    try {

        $questionsCollection = $db->questions;

        // 🔥 RANDOM 5 QUESTIONS
        $pipeline = [
            ['$match' => ['language' => $language]],
            ['$sample' => ['size' => 5]]
        ];

        $cursor = $questionsCollection->aggregate($pipeline);

        $questions = [];

        foreach ($cursor as $q) {
            $questions[] = [
                "questionId" => $q['questionId'],
                "question"   => $q['question'],
                "language"   => $q['language']
            ];
        }

        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => [
                    "count" => count($questions),
                    "data" => $questions
                ],
                "responseMessage" => "Questions fetched successfully",
                "responseFrom"    => "getQuestions"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Questions fetched successfully"
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode"    => 500,
                "responseData"    => null,
                "responseMessage" => $e->getMessage(),
                "responseFrom"    => "getQuestions"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "get questions api error"
        ]);
    }
}