<?php

require_once __DIR__ . '/../config/db.php';

function registerUser($data){
    global $userCollection;

    $data = [
        "name" => $data['name'],
        "age" => $data['age'] ?? null,
        "email" => $data['email'],
        "phone_number" => $data['phone_number'],
        "pincode" => $data['pincode'],
        "city" => $data['city'] ?? null,
        "state" => $data['state'] ?? null,
        "district" => $data['district'] ?? null,
        "address" => $data['address'],
        // "password" => $data['password'],
        "password" => password_hash($data['password'], PASSWORD_DEFAULT),
        // "confirm_password" => $data['confirm_password'],
        "confirm_password" => password_hash($data['confirm_password'], PASSWORD_DEFAULT),
        "createdAt" => new MongoDB\BSON\UTCDateTime(),
        "updatedAt" => new MongoDB\BSON\UTCDateTime()
    ];

    $userCollection->insertOne($data);
    return true;

}