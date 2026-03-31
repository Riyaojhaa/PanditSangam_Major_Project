<?php
header("Content-Type: application/json");

// default response template
function response($statusCode, $data, $message, $from = "addressAPI") {
    http_response_code($statusCode);
    echo json_encode([
        "apiResponseCode" => $statusCode,
        "apiResponseData" => [
            "responseCode" => $statusCode,
            "responseData" => $data,
            "responseMessage" => $message,
            "responseFrom" => $from
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => $message
    ]);
    exit;
}

// check pincode
if (!isset($_GET['pin']) || empty($_GET['pin'])) {
    response(400, null, "Pincode is required");
}

// ✅ assign once
$pin = $_GET['pin'];

// ✅ validate
if (!preg_match('/^[0-9]{6}$/', $pin)) {
    response(400, null, "Invalid pincode format. It must be 6 digit number.");
}

// API call
$url = "https://api.postalpincode.in/pincode/" . $pin;
$responseData = file_get_contents($url);

if ($responseData === FALSE) {
    response(500, null, "Unable to fetch address");
}

$data = json_decode($responseData, true);

// invalid pincode
if ($data[0]['Status'] != "Success") {
    response(404, null, "Invalid Pincode");
}

// extract data
$postOffice = $data[0]['PostOffice'][0];

$result = [
    "pincode" => $pin,
    "state" => $postOffice['State'],
    "district" => $postOffice['District'],
    "city" => $postOffice['Block'],
];

// success
response(200, $result, "Address fetched successfully");