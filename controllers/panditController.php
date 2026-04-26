<?php

require_once __DIR__ . '/../models/Pandit.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

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

    // URL se panditId lo — ?id=xxx
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

        // ObjectId ko string mein convert karo
        $pandit['_id']    = (string)$pandit['_id'];
        $pandit['userId'] = (string)$pandit['userId'];

        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => $pandit,
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

        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => $pandits,
                "responseMessage" => count($pandits) . " pandit(s) found",
                "responseFrom"    => "getAllPandits"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => count($pandits) . " pandit(s) found"
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