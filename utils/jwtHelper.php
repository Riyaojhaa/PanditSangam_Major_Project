<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateJWT($user){
    $key = "PANDIT_SANGAMA_APPLICATION_SECRET_KEY";
    $payload = [
        "iat" => time(),
        "exp" => time() + 86400, // 24 hours
        "data" => [
            "id" => (string)$user['_id'],
            "email" => $user['email']
        ]
    ];
    return JWT::encode($payload, $key, 'HS256');
}

function verifyJWT($token){
    $key = "PANDIT_SANGAMA_APPLICATION_SECRET_KEY";
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded->data;
    } catch (Exception $e) {
        return false;
    }
}

function getUserFromToken(){

    $headers = getallheaders();

    if(!isset($headers['Authorization'])){
        return null;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace("Bearer ", "", $authHeader);

    $data = verifyJWT($token);

    if(!$data){
        return null;
    }

    return $data->id;
}