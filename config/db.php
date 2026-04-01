<?php
require __DIR__ . '/../vendor/autoload.php';

// ✅ Direct URI hardcode karo — pehle test ke liye
$mongoUri = "mongodb+srv://rojha5250_db_user:vQwpNQFvRC1efW8j@cluster0.qvwfpec.mongodb.net/panditAppNew?retryWrites=true&w=majority&appName=Cluster0";

try {
    $client = new MongoDB\Client($mongoUri, [], [
        'tls' => true,
        'tlsAllowInvalidCertificates' => true,
        'tlsAllowInvalidHostnames' => true,
    ]);

    $db = $client->panditAppNew;
    $usersCollection = $db->users;
    $otpCollection = $db->otp;

} catch (Exception $e) {
    die(json_encode([
        "error" => "DB Connection failed",
        "message" => $e->getMessage()
    ]));
}