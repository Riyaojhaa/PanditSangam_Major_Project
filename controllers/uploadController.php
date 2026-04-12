<?php

require_once __DIR__ . '/../config/cloudinary.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/jwtHelper.php';

use MongoDB\BSON\ObjectId;

function uploadFile(){

    global $db;

    header("Content-Type: application/json");

    // 🔐 USER FROM TOKEN
    $userId = getUserFromToken();

    if(!$userId){
        http_response_code(401);
        echo json_encode([
            "apiResponseCode" => 401,
            "apiResponseData" => [
                "responseCode" => 401,
                "responseData" => null,
                "responseMessage" => "Unauthorized (Invalid token)",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Unauthorized"
        ]);
        return;
    }

    // ❌ FILE CHECK
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "No file uploaded",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "No file uploaded"
        ]);
        return;
    }

    $file = $_FILES['file'];
    $tmpPath = $file['tmp_name'];
    $originalName = $file['name'];
    $fileSize = $file['size'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $tmpPath);

    $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

    // ✅ VALIDATION
    $allowedTypes = [
        "image/jpeg","image/png","image/jpg",
        "video/mp4","video/mkv",
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ];

    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Invalid file type",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid file type"
        ]);
        return;
    }

    if ($fileSize > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "File too large",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "File too large"
        ]);
        return;
    }

    try {

        $cloudinary = getCloudinary();

        // ☁️ UPLOAD
        $upload = $cloudinary->uploadApi()->upload($tmpPath, [
            "resource_type" => "auto",
            "folder" => "user_uploads",
            "public_id" => pathinfo($fileName, PATHINFO_FILENAME)
        ]);

        $url = $upload['secure_url'];
        $publicId = $upload['public_id'];

        // 📂 CATEGORY
        $category = "other";
        if (str_contains($fileType, "image")) {
            $category = "image";
        } elseif (str_contains($fileType, "video")) {
            $category = "video";
        } elseif (str_contains($fileType, "application")) {
            $category = "document";
        }

        // 📦 SAVE HISTORY
        $uploadCollection = $db->uploads;

        $data = [
            "userId" => $userId,
            "fileName" => $fileName,
            "originalName" => $originalName,
            "fileType" => $fileType,
            "category" => $category,
            "url" => $url,
            "publicId" => $publicId,
            "size" => $fileSize,
            "createdAt" => date("Y-m-d H:i:s")
        ];

        $uploadCollection->insertOne($data);

        // 🎯 BUSINESS LOGIC
        $extraMessage = "";

        if($category === "image"){

            $userCollection = $db->users;

            $userCollection->updateOne(
                ["_id" => new ObjectId($userId)],
                ['$set' => ["profilePic" => $url]]
            );

            $extraMessage = "Profile pic updated";

        } elseif($category === "video"){

            // 🔍 check collection exists
            $collections = [];

            foreach ($db->listCollections() as $collection) {
                $collections[] = $collection->getName();
            }

            if(!in_array("pandits", $collections)){
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode" => 400,
                        "responseData" => null,
                        "responseMessage" => "Pandit DB not created yet",
                        "responseFrom" => "uploadFile"
                    ],
                    "apiResponseFrom" => "php",
                    "apiResponseMessage" => "Pandit DB missing"
                ]);
                return;
            }

            $panditCollection = $db->pandits;

            $panditCollection->insertOne([
                "userId" => $userId,
                "videoUrl" => $url,
                "createdAt" => date("Y-m-d H:i:s")
            ]);

            $extraMessage = "Video saved for pandit";
        }

        // ✅ SUCCESS RESPONSE
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode" => 200,
                "responseData" => $data,
                "responseMessage" => "Uploaded successfully. " . $extraMessage,
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Uploaded successfully"
        ]);

    } catch (Exception $e) {

        http_response_code(500);
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode" => 500,
                "responseData" => null,
                "responseMessage" => $e->getMessage(),
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => $e->getMessage()
        ]);
    }
}