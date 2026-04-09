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

} elseif ($request === '/api/v1/auth/send-otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    include $root . '/controllers/otpController.php';
    sendOtp();

} elseif ($request === '/api/v1/auth/verify-otp' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    include $root . '/controllers/otpController.php';
    verifyOtp();

} elseif ($request === '/api/v1/auth/user/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    include $root . '/controllers/registerController.php';
    register();

} elseif ($request === '/api/v1/auth/user/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    include $root . '/controllers/loginController.php';
    login();

} elseif ($request === '/api/v1/user/profile' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    include $root . '/controllers/profileController.php';
    getProfile();

} elseif ($request === '/api/v1/user/profile' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    include $root . '/controllers/profileController.php';
    updateProfile();

} else {
    http_response_code(404);
    echo json_encode([
        "error" => "Route not found",
        "route" => $request,
    ]);
}
