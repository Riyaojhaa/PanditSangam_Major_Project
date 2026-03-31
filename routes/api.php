<?php

$request = $_SERVER['REQUEST_URI'];

// remove query string
$request = explode('?', $request)[0];

// routing
$root = '/var/task/user';

if (preg_match("#^/api/v1/address/pincode/([^/]+)$#", $request, $matches)) {
    $_GET['pin'] = $matches[1];
    include $root . '/controllers/addressController.php';

} elseif ($request == '/api/v1/auth/send-otp') {
    include $root . '/controllers/otpController.php';
    sendOtp();

} elseif ($request == '/api/v1/auth/verify-otp') {
    include $root . '/controllers/otpController.php';
    verifyOtp();

} else {
    echo json_encode([
        "error" => "Route not found",
        "route" => $request
    ]);
}