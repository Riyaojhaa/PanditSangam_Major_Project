<?php
require __DIR__ . '/../vendor/autoload.php';

$mongoUri = "mongodb+srv://rojha5250_db_user:vQwpNQFvRC1efW8j@cluster0.qvwfpec.mongodb.net/panditAppNew?retryWrites=true&w=majority&appName=Cluster0&tls=true&tlsCAFile=/etc/ssl/certs/ca-certificates.crt";

try {
    $client = new MongoDB\Client($mongoUri);

    $db = $client->panditAppNew;
    $usersCollection = $db->users;
    $otpCollection = $db->otp;

} catch (Exception $e) {
    die(json_encode([
        "error" => "DB Connection failed",
        "message" => $e->getMessage()
    ]));
}