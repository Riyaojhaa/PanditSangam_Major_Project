<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

function panditAction(){
    $data = json_decode(file_get_contents("php://input"), true);

    $userId          = $data['userId'];
    $status          = $data['status'];
    $currentStep     = $data['currentStep'];
    $rejectionReason = $data['rejectionReason'] ?? null;

    global $userCollection;

    $set = [
        'panditOnboarding.currentStep'     => $currentStep,
        'panditOnboarding.status'          => $status,
        'panditOnboarding.rejectionReason' => $rejectionReason,
        'updatedAt'                        => new MongoDB\BSON\UTCDateTime()
    ];

    if ($status === 'approved') {
        $set['isPandit'] = true;
        $set['step']     = 0;
    } else {
        // ✅ Reject pe isPandit false karo
        $set['isPandit'] = false;
    }

    $userCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$set' => $set]
    );

    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode"    => 200,
            "responseData"    => null,
            "responseMessage" => "Action taken successfully",
            "responseFrom"    => "panditAction"
        ],
        "apiResponseFrom"    => "php",
        "apiResponseMessage" => "Action taken successfully"
    ]);
}