<?php
header("Content-Type: application/json");

require __DIR__ . '/../config/db.php';

try {

    // sample data (jo DB me jayega)
    $data = [
        "name" => "Riya",
        "email" => "riya@test.com",
        "createdAt" => new MongoDB\BSON\UTCDateTime()
    ];

    // INSERT
    $insertResult = $usersCollection->insertOne($data);

    // FETCH
    $user = $usersCollection->findOne([
        "email" => "riya@test.com"
    ]);

    echo json_encode([
        "success" => true,
        "insertedId" => (string)$insertResult->getInsertedId(),
        "user" => $user
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}