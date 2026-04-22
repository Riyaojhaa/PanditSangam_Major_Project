<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/jwtHelper.php';
require_once __DIR__ . '/../utils/geminiHelper.php';

use MongoDB\BSON\ObjectId;

function getPalmReading(){

    global $db;

    header("Content-Type: application/json");

    $userId = getUserFromToken();

    if(!$userId){
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode" => 401,
                "responseData" => null,
                "responseMessage" => "Unauthorized",
                "responseFrom" => "getPalmReading"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Unauthorized"
        ]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data['palmId'])){
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "palmId required",
                "responseFrom" => "getPalmReading"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "palmId required"
        ]);
        return;
    }

    $palmId = $data['palmId'];

    $record = $db->palmreadingimg->findOne([
        "_id" => new ObjectId($palmId),
        "userId" => $userId
    ]);

    if(!$record){
        http_response_code(404);
        echo json_encode([
            "apiResponseCode" => 404,
            "apiResponseData" => [
                "responseCode" => 404,
                "responseData" => null,
                "responseMessage" => "Palm image not found",
                "responseFrom" => "getPalmReading"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Palm image not found"
        ]);
        return;
    }

    // 🔁 Already processed check (optimization)
    if($record['status'] === "processed"){
    $result = $record['result'] ?? null;

    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => [
                "result" => $result,
                "cached" => true
            ],
            "responseMessage" => "Palm reading result fetched successfully",
            "responseFrom" => "getPalmReading"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Palm reading result fetched successfully"
    ]);
    return;
}

    $imageUrl = $record['imageUrl'];

    // 🤖 AI CALL (Gemini)
    $resultText = callGemini($imageUrl);

    // 💾 Save result
    $db->palmreadingimg->updateOne(
        ["_id" => new ObjectId($palmId)],
        ['$set' => [
            "status" => "processed",
            "result" => $resultText
        ]]
    );

    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => ["result" => $resultText, "cached" => false],
            "responseMessage" => "Palm reading result fetched successfully",
            "responseFrom" => "getPalmReading"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Palm reading result fetched successfully"
    ]);
}