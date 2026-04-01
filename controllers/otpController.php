<?php

$root = dirname(__DIR__);
require_once $root . '/models/otp.php';
require_once $root . '/utils/mailer.php';

// ✅ SEND OTP
function sendOtp() {

    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? null;

    // ❌ Email missing
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // HTTP 400 for error
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Email required",
                "responseFrom" => "sendOTP"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Email required"
        ]);
        return;
    }

    // ✅ Generate OTP
    $otp = generateOtp();

    // Save OTP in DB
    saveOtp($email, $otp);

    // Send HTML email
    $subject = "Your OTP Code - PanditSangam";
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>OTP Verification</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 50px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .header { text-align: center; font-size: 24px; font-weight: bold; color: #333333; }
            .content { margin-top: 20px; font-size: 16px; color: #555555; }
            .otp { display: block; margin: 20px auto; font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 4px; text-align: center; }
            .footer { margin-top: 30px; font-size: 14px; color: #888888; text-align: center; }
            .button { display: inline-block; padding: 10px 20px; margin-top: 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">Pandit Sangam</div>
            <div class="content">
                Hello,<br><br>
                Use the following OTP to complete your verification process. This OTP is valid for the next 5 minutes.
                <span class="otp">'.$otp.'</span>
                <br>
                If you did not request this, please ignore this email.
                <br><br>
            </div>
            <div class="footer">
                &copy; '.date("Y").' Pandit Sangam. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ';

    sendMail($email, $subject, $body);

    // ✅ Success response
    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => ["email" => $email],
            "responseMessage" => "OTP sent successfully",
            "responseFrom" => "sendOTP"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "OTP sent successfully"
    ]);
}


// ✅ VERIFY OTP
function verifyOtp() {

    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? null;
    $otp   = $data['otp'] ?? null;

    // ❌ Missing email or OTP
    if (!$email || !$otp || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{6}$/', $otp)) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Email & OTP are required",
                "responseFrom" => "verifyOTP"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Email & OTP are required"
        ]);
        return;
    }

    // ✅ Verify OTP in DB
    $isValid = verifyOtpFromDB($email, $otp);

    if ($isValid) {
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode" => 200,
                "responseData" => ["email" => $email],
                "responseMessage" => "OTP verified successfully",
                "responseFrom" => "verifyOTP"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "OTP verified successfully"
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "OTP not verified",
                "responseFrom" => "verifyOTP"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "OTP not verified"
        ]);
    }
}