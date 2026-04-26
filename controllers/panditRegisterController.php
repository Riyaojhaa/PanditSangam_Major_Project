<?php

require_once __DIR__ . '/../models/Pandit.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function panditRegister(){

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
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Token missing"
        ]);
        exit;
    }

    // ✅ Token extract & verify
    $token   = str_replace("Bearer ", "", $headers['Authorization']);
    $decoded = verifyJWT($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode"    => 401,
                "responseData"    => null,
                "responseMessage" => "Invalid token",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    $userId = $decoded->id;

    // ✅ Input data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "No data provided",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "No data provided"
        ]);
        exit;
    }

    // ✅ Required fields check
    if (!isset(
    $data['pooja_type'],
    $data['pooja_experience'],
    $data['certifications'],
    $data['aadhar_card_url'],
    $data['travel_pref'],
    $data['known_language'],
    $data['about']
    )) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode"    => 400,
            "responseData"    => null,
            "responseMessage" => "Pooja Type, Pooja Experience, Certifications, Aadhar Card URL, Travel Preference, Known Language — sab required hain",
            "responseFrom"    => "panditRegister"
        ],
        "apiResponseFrom"    => "php",
        "apiResponseMessage" => "All fields are required"
    ]);
    exit;
   }
   if (!is_array($data['certifications']) || empty($data['certifications'])) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode"    => 400,
            "responseData"    => null,
            "responseMessage" => "certification has to be array and it should contain valid url",
            "responseFrom"    => "panditRegister"
        ],
        "apiResponseFrom"    => "php",
        "apiResponseMessage" => "Certification has to be array and it should contain valid url"
    ]);
    exit;
}

    // ✅ Pandit already registered check
    $existingPandit = getPanditByUserId($userId);
    if ($existingPandit) {
        http_response_code(409);
        echo json_encode([
            "apiResponseCode" => 409,
            "apiResponseData" => [
                "responseCode"    => 409,
                "responseData"    => null,
                "responseMessage" => "Pandit already registered",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Pandit already registered"
        ]);
        exit;
    }

    // ✅ Pandit register karo
    $result = registerPandit($userId, $data);

    if ($result['success']) {

        // ✅ User table mein step: 2 update karo
        global $userCollection;
        $userCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($userId)],
            ['$set' => [
                'step'                          => 2,
                'panditOnboarding.currentStep'  => 2,
                'panditOnboarding.status'       => 'pandit_registered',
                'panditOnboarding.rejectionReason' => null,
                'updatedAt'                     => new MongoDB\BSON\UTCDateTime()
            ]]
        );

        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => [
                    "panditId" => $result['panditId'],
                    "step"     => 2        // ✅ Step 2 response mein
                ],
                "responseMessage" => "Pandit registered successfully",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Pandit registered successfully"
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Pandit not registered",
                "responseFrom"    => "panditRegister"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Pandit not registered"
        ]);
    }
}