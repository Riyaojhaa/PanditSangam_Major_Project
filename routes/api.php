<?php
$request = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$request = ($request === '' || $request === false) ? '/' : $request;

// Strip subfolder / vhost prefix so /PanditAppNew/.../api/v1/foo → /api/v1/foo
if (($pos = strpos($request, '/api/')) !== false) {
    $request = substr($request, $pos);
}
$request = rtrim($request, '/') ?: '/';

$root = dirname(__DIR__);

if (preg_match("#^/api/v1/address/pincode/([^/]+)$#", $request, $matches)) {
    $_GET['pin'] = $matches[1];
    include $root . '/controllers/addressController.php';

} elseif ($request === '/api/v1/auth/send-otp') {
    include $root . '/controllers/otpController.php';
    sendOtp();

} elseif ($request === '/api/v1/auth/verify-otp') {
    include $root . '/controllers/otpController.php';
    verifyOtp();

} else {
    http_response_code(404);
    echo json_encode([
        "error" => "Route not found",
        "route" => $request,
    ]);
}
