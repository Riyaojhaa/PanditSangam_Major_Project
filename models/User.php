<?php

require_once __DIR__ . '/../config/db.php';

function registerUser($data){
    global $userCollection;

    $insertData = [
        "name"         => $data['name'],
        "age"          => $data['age'] ?? null,
        "email"        => $data['email'],
        "phone_number" => $data['phone_number'],
        "pincode"      => $data['pincode'],
        "city"         => $data['city'] ?? null,
        "state"        => $data['state'] ?? null,
        "district"     => $data['district'] ?? null,
        "address"      => $data['address'],
        "password"     => password_hash($data['password'], PASSWORD_DEFAULT),
        "createdAt"    => new MongoDB\BSON\UTCDateTime(),
        "updatedAt"    => new MongoDB\BSON\UTCDateTime()
    ];

    $userCollection->insertOne($insertData);
    return true;
}

function findUserByEmail($email){
    global $userCollection;
    return $userCollection->findOne(['email' => $email]);
}

function findUserByPhone($phone_number){
    global $userCollection;
    return $userCollection->findOne(['phone_number' => $phone_number]);
}

function getUserById($id){
    global $userCollection;
    return $userCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
}

function updateUser($id, $data){
    global $userCollection;
    
    $updateData = [];

    if(isset($data['name']))         $updateData['name']         = $data['name'];
    if(isset($data['age']))          $updateData['age']          = $data['age'];
    if(isset($data['email']))        $updateData['email']        = $data['email'];
    if(isset($data['phone_number'])) $updateData['phone_number'] = $data['phone_number'];
    if(isset($data['pincode']))      $updateData['pincode']      = $data['pincode'];
    if(isset($data['city']))         $updateData['city']         = $data['city'];
    if(isset($data['state']))        $updateData['state']        = $data['state'];
    if(isset($data['district']))     $updateData['district']     = $data['district'];
    if(isset($data['address']))      $updateData['address']      = $data['address'];

    $updateData['updatedAt'] = new MongoDB\BSON\UTCDateTime();

    $result = $userCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => $updateData]
    );

    return $result->getModifiedCount() > 0;
}