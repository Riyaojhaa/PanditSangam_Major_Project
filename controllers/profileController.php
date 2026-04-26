<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pandit.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function getProfile(){

    $headers = getallheaders();

    // ✅ check token in header
    if (!isset($headers['Authorization'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Token missing",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Token missing"
        ]);
        exit;
    }

    // ✅ extract token
    $token = str_replace("Bearer ", "", $headers['Authorization']);

    // ✅ verify token
    $decoded = verifyJWT($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode" => 401,
                "responseData" => null,
                "responseMessage" => "Invalid token",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    // ✅ get userId from token
    $userId = $decoded->id;

    // ✅ fetch user
    $user = getUserById($userId);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "apiResponseCode" => 404,
            "apiResponseData" => [
                "responseCode" => 404,
                "responseData" => null,
                "responseMessage" => "User not found",
                "responseFrom" => "getProfile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "User not found"
        ]);
        exit;
    }

    // ✅ User ka base data
    $userData = [
    "id"               => (string)$user['_id'],
    "name"             => $user['name'] ?? "",
    "age"              => $user['age'] ?? "",
    "email"            => $user['email'] ?? "",
    "phone_number"     => $user['phone_number'] ?? "",
    "pincode"          => $user['pincode'] ?? "",
    "address"          => $user['address'] ?? "",
    "city"             => $user['city'] ?? "",
    "state"            => $user['state'] ?? "",
    "district"         => $user['district'] ?? "",
    "step"             => $user['step'] ?? 1,
    "isPandit"         => $user['isPandit'] ?? false,
    "panditOnboarding" => [ 
        "currentStep"     => $user['panditOnboarding']['currentStep'] ?? 1,
        "status"          => $user['panditOnboarding']['status'] ?? "user_registered",
        "rejectionReason" => $user['panditOnboarding']['rejectionReason'] ?? null
    ]
];

    $pandit = getPanditByUserId($userId);

    if ($pandit) {
        // ✅ Pandit data bhi merge karo
        $userData['panditData'] = [
        "panditId"        => (string)$pandit['_id'],
        "poojaType"       => $pandit['poojaType'] ?? "",
        "poojaExperience" => $pandit['poojaExperience'] ?? 0,
        "certifications"  => $pandit['certifications'] ?? [],
        "aadharCardUrl"   => $pandit['aadharCardUrl'] ?? null,
        "travelPref"      => $pandit['travelPref'] ?? "",
        "knownLanguage"   => $pandit['knownLanguage'] ?? "",
        "about"           => $pandit['about'] ?? "",
    ];
    }

    // ✅ response
    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode"    => 200,
            "responseData"    => [
                "user" => $userData
            ],
            "responseMessage" => "profile fetched successfully",
            "responseFrom"    => "getProfile"
        ],
        "apiResponseFrom"    => "php",
        "apiResponseMessage" => "profile fetched successfully"
    ]);
}


function updateProfile(){

    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "Token missing",
                "responseFrom"    => "updateProfile"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Token missing"
        ]);
        exit;
    }

    $token   = str_replace("Bearer ", "", $headers['Authorization']);
    $decoded = verifyJWT($token);

    if (!$decoded) {
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode"    => 401,
                "responseData"    => null,
                "responseMessage" => "Invalid token",
                "responseFrom"    => "updateProfile"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "Invalid token"
        ]);
        exit;
    }

    $userId = $decoded->id;

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode"    => 400,
                "responseData"    => null,
                "responseMessage" => "No data provided",
                "responseFrom"    => "updateProfile"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => "No data provided"
        ]);
        exit;
    }

    // ✅ User data update — agar user fields aaye hain to
    $userFields = ['name', 'age', 'email', 'phone_number', 'pincode', 'city', 'state', 'district', 'address'];
    $userUpdateData = [];

    foreach ($userFields as $field) {
        if (isset($data[$field])) {
            $userUpdateData[$field] = $data[$field];
        }
    }

    if (!empty($userUpdateData)) {
        updateUser($userId, $userUpdateData);
    }

    // ✅ Pandit data update — agar pandit fields aaye hain to
    $panditFields = ['pooja_type', 'pooja_experience', 'certifications', 'aadhar_card_url', 'travel_pref', 'known_language', 'about'];
    $panditUpdateData = [];

    foreach ($panditFields as $field) {
        if (isset($data[$field])) {
            $panditUpdateData[$field] = $data[$field];
        }
    }
    // ✅ TEMPORARILY debug karo
// error_log("panditUpdateData: " . print_r($panditUpdateData, true));

if (!empty($panditUpdateData)) {
    $pandit = getPanditByUserId($userId);

    // ✅ TEMPORARILY — file mein likho
    file_put_contents(
        "C:/xampp/htdocs/PanditAppNew/debug.txt",
        "userId: " . $userId . "\n" .
        "panditUpdateData: " . print_r($panditUpdateData, true) . "\n" .
        "pandit: " . print_r($pandit, true) . "\n"
    );
    error_log("pandit found: " . print_r($pandit, true));

    if ($pandit) {
        $result = updatePandit($userId, $panditUpdateData);
        error_log("updatePandit result: " . var_export($result, true));
    }
}

    if (!empty($panditUpdateData)) {
        // ✅ Pehle check karo ye user pandit hai ya nahi
        $pandit = getPanditByUserId($userId);

        if ($pandit) {
            updatePandit($userId, $panditUpdateData);   // ✅ Pandit update karo
        }
        // Agar pandit nahi hai to pandit fields ignore ho jayengi
    }

    // ✅ Updated user + pandit data fetch karke return karo
    $user = getUserById($userId);
    $pandit = getPanditByUserId($userId);

    $userData = [
        "id"           => (string)$user['_id'],
        "name"         => $user['name'] ?? "",
        "age"          => $user['age'] ?? "",
        "email"        => $user['email'] ?? "",
        "phone_number" => $user['phone_number'] ?? "",
        "pincode"      => $user['pincode'] ?? "",
        "address"      => $user['address'] ?? "",
        "city"         => $user['city'] ?? "",
        "state"        => $user['state'] ?? "",
        "district"     => $user['district'] ?? "",
        "step"             => $user['step'] ?? 1,
        "isPandit"         => $user['isPandit'] ?? false,
        "panditOnboarding" => [ 
            "currentStep"     => $user['panditOnboarding']['currentStep'] ?? 1,
            "status"          => $user['panditOnboarding']['status'] ?? "user_registered",
            "rejectionReason" => $user['panditOnboarding']['rejectionReason'] ?? null
        ]
    ];

    if ($pandit) {
        $userData['panditData'] = [
            "panditId"        => (string)$pandit['_id'],
        "poojaType"       => $pandit['poojaType'] ?? "",
        "poojaExperience" => $pandit['poojaExperience'] ?? 0,
        "certifications"  => $pandit['certifications'] ?? [],
        "aadharCardUrl"   => $pandit['aadharCardUrl'] ?? null,
        "travelPref"      => $pandit['travelPref'] ?? "",
        "knownLanguage"   => $pandit['knownLanguage'] ?? "",
        "about"           => $pandit['about'] ?? "",
        ];
    }

    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode"    => 200,
            "responseData"    => [
                "user" => $userData     
            ],
            "responseMessage" => "Profile updated successfully",
            "responseFrom"    => "updateProfile"
        ],
        "apiResponseFrom"    => "php",
        "apiResponseMessage" => "Profile updated successfully"
    ]);
}