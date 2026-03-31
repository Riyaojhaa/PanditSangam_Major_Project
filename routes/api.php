<?php

$request = $_SERVER['REQUEST_URI'];

// remove query string
$request = explode('?', $request)[0];

// routing
if (preg_match("#^/api/v1/address/pincode/([^/]+)$#", $request, $matches)) {

    $_GET['pin'] = $matches[1];
    include dirname(__DIR__) . '/controllers/addressController.php';

} elseif ($request == '/api/v1/auth/send-otp') {

    include dirname(__DIR__) . '/controllers/otpController.php';
    sendOtp();

} elseif ($request == '/api/v1/auth/verify-otp') {

    include dirname(__DIR__) . '/controllers/otpController.php';
    verifyOtp();

} else {
    echo json_encode([
        "error" => "Route not found",
        "route" => $request
    ]);
}