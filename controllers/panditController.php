<?php

require_once __DIR__ . '/../models/Pandit.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

// ✅ Helper — pandit + user data build karo
function buildPanditUserResponse($pandit) {
    $userId = (string)$pandit['userId'];
    $user   = getUserById($userId);

    return [
        "user" => [
            "id"           => (string)$user['_id'],
            "name"         => $user['name'] ?? "",
            "age"          => $user['age'] ?? "",
            "email"        => $user['email'] ?? "",
            "phone_number" => $user['phone_number'] ?? "",
            "pincode"      => $user['pincode'] ?? "",
            "address"      => $user['address'] ?? "",
            "city"         => $user['city'] ?? "",
            "state"        => $user['state'] ?? "",
            "district"     => $user['district'] ?? "",
            "step"         => $user['step'] ?? 1,
            "isPandit"     => $user['isPandit'] ?? false,
            "panditOnboarding" => [
                "currentStep"     => $user['panditOnboarding']['currentStep'] ?? 1,
                "status"          => $user['panditOnboarding']['status'] ?? "user_registered",
                "rejectionReason" => $user['panditOnboarding']['rejectionReason'] ?? null
            ],
            "panditData" => [
                "panditId"        => (string)$pandit['_id'],
                "poojaType"       => $pandit['poojaType'] ?? "",
                "poojaExperience" => $pandit['poojaExperience'] ?? 0,
                "certifications"  => $pandit['certifications'] ?? [],
                "aadharCardUrl"   => $pandit['aadharCardUrl'] ?? null,
                "travelPref"      => $pandit['travelPref'] ?? "",
                "knownLanguage"   => $pandit['knownLanguage'] ?? "",
                "about"           => $pandit['about'] ?? "",
            ]
        ]
    ];
}

// =====================================================
// 🔍 GET PANDIT BY ID
// =====================================================
function getPanditByIdController(){

    header("Content-Type: application/json");

    $userId = getUserFromToken();
    if(!$userId){
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode"    => 401,
                "responseData"    => null,
                "responseMessage" => "Unauthorized",
                "responseFrom"    => "getPanditById"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Unauthorized"
        ]);
        return;
    }

    $panditId = $_GET['id'] ?? null;

    if(!$panditId){
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Pandit ID required",
                "responseFrom"    => "getPanditById"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Pandit ID required"
        ]);
        return;
    }

    try {
        $pandit = getPanditById($panditId);

        if(!$pandit){
            http_response_code(404);
            echo json_encode([
                "apiResponseCode" => 404,
                "apiResponseData" => [
                    "responseCode"    => 404,
                    "responseData"    => null,
                    "responseMessage" => "Pandit not found",
                    "responseFrom"    => "getPanditById"
                ],
                "apiResponseFrom"    => "php",
                "apiResponseMessage" => "Pandit not found"
            ]);
            return;
        }

        // ✅ Pandit ki userId se user fetch karke merge karo
        $responseData = buildPanditUserResponse($pandit);

        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => $responseData,
                "responseMessage" => "Pandit fetched successfully",
                "responseFrom"    => "getPanditById"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Pandit fetched successfully"
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode"    => 500,
                "responseData"    => null,
                "responseMessage" => $e->getMessage(),
                "responseFrom"    => "getPanditById"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => $e->getMessage()
        ]);
    }
}

// =====================================================
// 📋 GET ALL PANDITS
// =====================================================
function getAllPanditsController(){

    header("Content-Type: application/json");

    $userId = getUserFromToken();
    if(!$userId){
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode"    => 401,
                "responseData"    => null,
                "responseMessage" => "Unauthorized",
                "responseFrom"    => "getAllPandits"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Unauthorized"
        ]);
        return;
    }

    try {
        $pandits = getAllPandits();

        // ✅ Har pandit ke liye userId se user fetch karke merge karo
        $result = [];
        foreach ($pandits as $pandit) {
            $result[] = buildPanditUserResponse($pandit);
        }

        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => ["pandits" => $result],
                "responseMessage" => count($result) . " pandit(s) found",
                "responseFrom"    => "getAllPandits"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => count($result) . " pandit(s) found"
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode"    => 500,
                "responseData"    => null,
                "responseMessage" => $e->getMessage(),
                "responseFrom"    => "getAllPandits"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => $e->getMessage()
        ]);
    }
}