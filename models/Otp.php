<?php

require_once __DIR__ . '/../config/db.php';

function generateOtp() {
    return rand(100000, 999999);
}

function saveOtp($email, $otp) {
    global $otpCollection;

    $expiry = new MongoDB\BSON\UTCDateTime((time() + 300) * 1000); // 5 min

    // 👉 ek email pe ek hi OTP (update)
    $otpCollection->updateOne(
        ["email" => $email],
        [
            '$set' => [
                "otp" => $otp,
                "expiry" => $expiry
            ]
        ],
        ["upsert" => true]
    );
}

function verifyOtpFromDB($email, $otp) {
    global $otpCollection;

    $currentTime = new MongoDB\BSON\UTCDateTime(time() * 1000);

    $result = $otpCollection->findOne([
        "email" => $email,
        "otp" => (int)$otp,
        "expiry" => ['$gt' => $currentTime]
    ]);

    return $result ? true : false;
}