<?php

require_once __DIR__ . '/../models/User.php';

function register(){
    $data = json_decode(file_get_contents("php://input"), true);

    // Check - data aaya ya nahi
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "No data received",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "No data received"
        ]);
        exit;
    }

    // Check - required fields
    if (!isset($data['name'], $data['email'], $data['phone_number'], $data['pincode'], $data['address'], $data['password'], $data['confirm_password'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Name, Email, Phone Number, Pincode, Address, Password, Confirm Password required",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Name, Email, Phone Number, Pincode, Address, Password, Confirm Password required"
        ]);
        exit;
    }

    // Check - password match
    if($data['password'] !== $data['confirm_password']){
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Passwords do not match",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Passwords do not match"
        ]);
        return;
    }

    // Check - email already registered
    $existingEmail = findUserByEmail($data['email']);
    if($existingEmail){
        http_response_code(409);
        echo json_encode([
            "apiResponseCode" => 409,
            "apiResponseData" => [
                "responseCode"    => 409,
                "responseData"    => null,
                "responseMessage" => "Email already registered",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Email already registered"
        ]);
        return;
    }

    // Check - phone number already registered
    $existingPhone = findUserByPhone($data['phone_number']);
    if($existingPhone){
        http_response_code(409);
        echo json_encode([
            "apiResponseCode" => 409,
            "apiResponseData" => [
                "responseCode"    => 409,
                "responseData"    => null,
                "responseMessage" => "Phone number already registered",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Phone number already registered"
        ]);
        return;
    }

    // Register user
    $user = registerUser($data);
    if($user){
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode"    => 200,
                "responseData"    => $user,
                "responseMessage" => "User registered successfully",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "User registered successfully"
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "User not registered",
                "responseFrom"    => "register"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "User not registered"
        ]);
    }
}

