<?php
require_once __DIR__ . '/bootstrap.php';

$mongoUri = $_ENV['MONGO_URI'] ?? $_SERVER['MONGO_URI'] ?? null;
if (!is_string($mongoUri) || $mongoUri === '') {
    $g = getenv('MONGO_URI');
    $mongoUri = ($g !== false && $g !== '') ? $g : 'mongodb+srv://rojha5250_db_user:vQwpNQFvRC1efW8j@cluster0.qvwfpec.mongodb.net/panditAppNew?retryWrites=true&w=majority&appName=Cluster0';
}

try {
    $client = new MongoDB\Client($mongoUri, [
        'tls' => true,
        'tlsAllowInvalidCertificates' => true,
        'authSource' => 'admin',
    ]);

    // Test connection immediately
    $client->listDatabases();

    $db = $client->panditAppNew;
    $userCollection = $db->users;
    $otpCollection = $db->otp;
    $uploadCollection = $db->uploads;

} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode([
        "error" => "DB Connection failed",
        "message" => $e->getMessage(),
        "hint" => "Check if your IP is whitelisted in MongoDB Atlas (add 0.0.0.0/0 for Vercel)."
    ]);
    exit;
}