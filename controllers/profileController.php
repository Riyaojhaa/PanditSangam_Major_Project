<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function getProfile(){

    $headers = getallheaders();

    // ✅ check token in header
    if (!isset($headers['Authorization'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Token missing",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Token missing"
        ]);
        exit;
    }

    // ✅ extract token
    $token = str_replace("Bearer ", "", $headers['Authorization']);

    // ✅ verify token
    $decoded = verifyJWT($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode" => 401,
                "responseData" => null,
                "responseMessage" => "Invalid token",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    // ✅ get userId from token
    $userId = $decoded->id;

    // ✅ fetch user
    $user = getUserById($userId);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "apiResponseCode" => 404,
            "apiResponseData" => [
                "responseCode" => 404,
                "responseData" => null,
                "responseMessage" => "User not found",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "User not found"
        ]);
        exit;
    }

    // ✅ response
    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => [
                "user" => [
                    "id" => (string)$user['_id'],
                    "name" => $user['name'] ?? "",
                    "age" => $user['age'] ?? "",
                    "email" => $user['email'] ?? "",
                    "phone_number" => $user['phone_number'] ?? "",
                    "pincode" => $user['pincode'] ?? "",
                    "address" => $user['address'] ?? "",
                    "city" => $user['city'] ?? "",
                    "state" => $user['state'] ?? "",
                    "district" => $user['district'] ?? ""
                ]
            ],
            "responseMessage" => "User profile fetched successfully",
            "responseFrom" => "getProfile"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "User profile fetched successfully"
    ]);
}

function updateProfile(){

    $headers = getallheaders();

    // ✅ check token
    if (!isset($headers['Authorization'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Token missing",
                "responseFrom" => "updateProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Token missing"
        ]);
        exit;
    }

    // ✅ extract token
    $token = str_replace("Bearer ", "", $headers['Authorization']);

    // ✅ verify token
    $decoded = verifyJWT($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode" => 401,
                "responseData" => null,
                "responseMessage" => "Invalid token",
                "responseFrom" => "updateProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    // ✅ get userId
    $userId = $decoded->id;

    // ✅ get input data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "No data provided",
                "responseFrom" => "updateProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "No data provided"
        ]);
        exit;
    }

    // ✅ update user
    $updated = updateUser($userId, $data);

    if ($updated) {
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode" => 200,
                "responseData" => null,
                "responseMessage" => "Profile updated successfully",
                "responseFrom" => "updateProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Profile updated successfully"
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Profile not updated",
                "responseFrom" => "updateProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Profile not updated"
        ]);
    }
}