<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function login(){
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "No data received",
            "responseFrom" => "login"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "No data received"
    ]);
    exit;
    }

    if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "Email & Password required",
            "responseFrom" => "login"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Email & Password required"
    ]);
    exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    $user = findUserByEmail($email);
    if($user){
        if(password_verify($password, $user['password'])){

            $token = generateJWT($user);
            http_response_code(200);
            echo json_encode([
                "apiResponseCode" => 200,
                "apiResponseData" => [
                    "responseCode" => 200,
                    "responseData" => [
                        "token" => $token,
                        "user" => [
                            "id" => (string)$user['_id'],
                            "name" => $user['name'],
                            "email" => $user['email'],
                            "isPandit" => $user['isPandit']
                        ]
                    ],
                    "responseMessage" => "User logged in successfully",
                    "responseFrom" => "login"
                ],
                "apiResponseFrom" => "php",
                "apiResponseMessage" => "User logged in successfully"
            ]);
        }else{
            http_response_code(400);
            echo json_encode([
                "apiResponseCode" => 400,
                "apiResponseData" => [
                    "responseCode" => 400,
                    "responseData" => null,
                    "responseMessage" => "Invalid password",
                    "responseFrom" => "login"
                ],
                "apiResponseFrom" => "php",
                "apiResponseMessage" => "Invalid password"
            ]);
        }
    }else{
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "User not found",
                "responseFrom" => "login"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "User not found"
        ]);
    }
}
