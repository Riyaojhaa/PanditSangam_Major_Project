<?php

require_once __DIR__ . '/../models/User.php';

function register(){
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "No data received",
            "responseFrom" => "register"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "No data received"
    ]);
    exit;
    }

    if (!isset($data['name'], $data['email'], $data['phone_number'], $data['pincode'], $data['address'], $data['password'], $data['confirm_password'])) {
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "Name, Email, Phone Number, Pincode, Address, Password, Confirm Password required",
            "responseFrom" => "register"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Name, Email, Phone Number, Pincode, Address, Password, Confirm Password required"
    ]);
    exit;
    }

    $name = $data['name'];
    $age = $data['age'];
    $email = $data['email'];
    $phone_number = $data['phone_number'];
    $pincode = $data['pincode'];
    $city = $data['city'];
    $state = $data['state'];
    $district = $data['district'];
    $address = $data['address'];
    $password = $data['password'];
    $confirm_password = $data['confirm_password'];

    if($password !== $confirm_password){
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Passwords do not match",
                "responseFrom" => "register"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Passwords do not match"
        ]);
        return;
    }

    $user = registerUser($data);
    if($user){
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode" => 200,
                "responseData" => $user,
                "responseMessage" => "User registered successfully",
                "responseFrom" => "register"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "User registered successfully"
        ]);
    }else{
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "User not registered",
                "responseFrom" => "register"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "User not registered"
        ]);
    }
}

