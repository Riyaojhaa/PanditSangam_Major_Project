<?php
require __DIR__ . '/../vendor/autoload.php';

try {
    // $client = new MongoDB\Client("mongodb://127.0.0.1:27017");
    $client = new MongoDB\Client(getenv('MONGO_URI'));

    $client = new MongoDB\Client($uri, [], [
        'tls' => true,
        'tlsAllowInvalidCertificates' => true,
        'tlsAllowInvalidHostnames' => true,
    ]);
    // DB name
    $db = $client->panditAppNew;

    // Collections
    $usersCollection = $db->users;
    $otpCollection = $db->otp;

} catch (Exception $e) {
    die(json_encode([
        "error" => "DB Connection failed",
        "message" => $e->getMessage()
    ]));
}