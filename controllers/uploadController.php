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
                "responseMessage" => "Unauthorized",
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

    // 📂 CATEGORY FROM BODY (required)
    $category = isset($_POST['category']) ? trim($_POST['category']) : null;

    $allowedCategories = ["profilepic", "certificates", "panditvideo"];

    if (!$category || !in_array($category, $allowedCategories)) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => "Invalid or missing category. Allowed: profilepic, certificates, panditvideo",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Invalid or missing category"
        ]);
        return;
    }

    $files = $_FILES['file'];

    // Normalize to array format (even if single file)
    $isMultiple = is_array($files['name']);
    if (!$isMultiple) {
        $files['name']     = [$files['name']];
        $files['tmp_name'] = [$files['tmp_name']];
        $files['size']     = [$files['size']];
        $files['type']     = [$files['type']];
        $files['error']    = [$files['error']];
    }

    // 🚫 profilepic and panditvideo: only 1 file allowed
    if (in_array($category, ["profilepic", "panditvideo"]) && count($files['name']) > 1) {
        http_response_code(400);
        echo json_encode([
            "apiResponseCode" => 400,
            "apiResponseData" => [
                "responseCode" => 400,
                "responseData" => null,
                "responseMessage" => ucfirst($category) . " allows only 1 file at a time",
                "responseFrom" => "uploadFile"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => ucfirst($category) . " allows only 1 file at a time"
        ]);
        return;
    }

    try {

        $cloudinary = getCloudinary();

        // =====================================================
        // 🖼️ PROFILEPIC — single image only
        // =====================================================
        if ($category === "profilepic") {

            $tmpPath      = $files['tmp_name'][0];
            $originalName = $files['name'][0];
            $fileSize     = $files['size'][0];

            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);

            $allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];

            if (!in_array($fileType, $allowedImageTypes)) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => null,
                        "responseMessage" => "Profile pic must be an image (jpeg/png/jpg). Got: " . $fileType,
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Profile pic must be an image"
                ]);
                return;
            }

            if ($fileSize > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => null,
                        "responseMessage" => "Profile pic must be under 5MB",
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Profile pic must be under 5MB"
                ]);
                return;
            }

            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

            $upload   = $cloudinary->uploadApi()->upload($tmpPath, [
                "resource_type" => "image",
                "folder"        => "user_uploads/profilepics",
                "public_id"     => uniqid() . "_" . pathinfo($fileName, PATHINFO_FILENAME)
            ]);

            $url      = $upload['secure_url'];
            $publicId = $upload['public_id'];

            // 🗑️ Delete old profile pic from Cloudinary
            $userCollection = $db->users;
            $user = $userCollection->findOne(["_id" => new ObjectId($userId)]);
            $oldPublicId = $user['profilePicPublicId'] ?? null;
            if ($oldPublicId) {
                $cloudinary->uploadApi()->destroy($oldPublicId);
            }

            // 💾 Update user
            $userCollection->updateOne(
                ["_id" => new ObjectId($userId)],
                ['$set' => [
                    "profilePic"         => $url,
                    "profilePicPublicId" => $publicId
                ]]
            );

            // 📦 Save in uploads history
            $db->uploads->insertOne([
                "userId"       => $userId,
                "fileName"     => $fileName,
                "originalName" => $originalName,
                "fileType"     => $fileType,
                "category"     => "profilepic",
                "url"          => $url,
                "publicId"     => $publicId,
                "size"         => $fileSize,
                "createdAt"    => date("Y-m-d H:i:s")
            ]);

            http_response_code(200);
            echo json_encode([
                "apiResponseCode" => 200,
                "apiResponseData" => [
                    "responseCode"    => 200,
                    "responseData"    => ["url" => $url, "publicId" => $publicId],
                    "responseMessage" => "Profile pic updated successfully",
                    "responseFrom"    => "uploadFile"
                ],
                "apiResponseFrom"    => "php",
                "apiResponseMessage" => "Profile pic updated successfully"
            ]);
            return;
        }

        // =====================================================
        // 📄 CERTIFICATES — multiple PDFs only
        // =====================================================
        if ($category === "certificates") {

            $pdfUrls       = [];
            $savedDocs     = [];
            $rejectedFiles = [];

            $finfo = finfo_open(FILEINFO_MIME_TYPE); // ✅ Loop ke bahar

            for ($i = 0; $i < count($files['name']); $i++) {

                $tmpPath      = $files['tmp_name'][$i];
                $originalName = $files['name'][$i];
                $fileSize     = $files['size'][$i];
                $fileType     = finfo_file($finfo, $tmpPath);

                // ❌ Only PDF allowed
                if ($fileType !== "application/pdf") {
                    $rejectedFiles[] = $originalName . " (invalid type: " . $fileType . ")";
                    continue;
                }

                // 📏 10MB limit
                if ($fileSize > 10 * 1024 * 1024) {
                    $rejectedFiles[] = $originalName . " (too large, max 10MB)";
                    continue;
                }

                $fileName = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

                $upload = $cloudinary->uploadApi()->upload($tmpPath, [
                    "resource_type" => "auto",
                    "folder"        => "user_uploads/certificates",
                    "public_id"     => uniqid() . "_" . pathinfo($fileName, PATHINFO_FILENAME)
                ]);

                $pdfUrls[]   = $upload['secure_url'];
                $savedDocs[] = [
                    "userId"       => $userId,
                    "fileName"     => $fileName,
                    "originalName" => $originalName,
                    "fileType"     => $fileType,
                    "category"     => "certificates",
                    "url"          => $upload['secure_url'],
                    "publicId"     => $upload['public_id'],
                    "size"         => $fileSize,
                    "createdAt"    => date("Y-m-d H:i:s")
                ];
            }

            finfo_close($finfo);

            // ❌ Koi bhi valid PDF nahi
            if (empty($pdfUrls)) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => ["rejectedFiles" => $rejectedFiles],
                        "responseMessage" => "No valid PDFs found. Only PDF files under 10MB accepted. Rejected: " . implode(", ", $rejectedFiles),
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "No valid PDFs found"
                ]);
                return;
            }

            // 💾 Save valid docs
            foreach ($savedDocs as $doc) {
                $db->uploads->insertOne($doc);
            }
            $db->documents->insertOne([
                "userId"    => $userId,
                "category"  => "certificates",
                "pdfUrls"   => $pdfUrls,
                "createdAt" => date("Y-m-d H:i:s")
            ]);

            // ⚠️ Partial success — kuch PDFs thi, kuch invalid files bhi thi
            if (!empty($rejectedFiles)) {
                http_response_code(207);
                echo json_encode([
                    "apiResponseCode" => 207,
                    "apiResponseData" => [
                        "responseCode"    => 207,
                        "responseData"    => [
                            "uploadedUrls"  => $pdfUrls,
                            "rejectedFiles" => $rejectedFiles
                        ],
                        "responseMessage" => count($pdfUrls) . " PDF(s) uploaded. " . count($rejectedFiles) . " file(s) rejected (only PDFs allowed): " . implode(", ", $rejectedFiles),
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Partial upload — some files rejected"
                ]);
                return;
            }

            // ✅ Sab PDFs valid
            http_response_code(200);
            echo json_encode([
                "apiResponseCode" => 200,
                "apiResponseData" => [
                    "responseCode"    => 200,
                    "responseData"    => $pdfUrls,
                    "responseMessage" => count($pdfUrls) . " certificate(s) uploaded successfully",
                    "responseFrom"    => "uploadFile"
                ],
                "apiResponseFrom"    => "php",
                "apiResponseMessage" => count($pdfUrls) . " certificate(s) uploaded successfully"
            ]);
            return;
        }

        // =====================================================
        // 🎥 PANDITVIDEO — single video only
        // =====================================================
        if ($category === "panditvideo") {

            $tmpPath      = $files['tmp_name'][0];
            $originalName = $files['name'][0];
            $fileSize     = $files['size'][0];

            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);

            $allowedVideoTypes = ["video/mp4", "video/mkv", "video/x-matroska"];

            if (!in_array($fileType, $allowedVideoTypes)) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => null,
                        "responseMessage" => "Pandit video must be mp4 or mkv. Got: " . $fileType,
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Pandit video must be mp4 or mkv"
                ]);
                return;
            }

            if ($fileSize > 25 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => null,
                        "responseMessage" => "Pandit video must be under 25MB",
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Pandit video must be under 25MB"
                ]);
                return;
            }

            // 🔍 Check pandits collection exists
            $collections = [];
            foreach ($db->listCollections() as $col) {
                $collections[] = $col->getName();
            }

            if (!in_array("pandits", $collections)) {
                http_response_code(400);
                echo json_encode([
                    "apiResponseCode" => 400,
                    "apiResponseData" => [
                        "responseCode"    => 400,
                        "responseData"    => null,
                        "responseMessage" => "Pandit DB not created yet",
                        "responseFrom"    => "uploadFile"
                    ],
                    "apiResponseFrom"    => "php",
                    "apiResponseMessage" => "Pandit DB missing"
                ]);
                return;
            }

            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $originalName);

            $upload   = $cloudinary->uploadApi()->upload($tmpPath, [
                "resource_type" => "video",
                "folder"        => "user_uploads/pandits",
                "public_id"     => uniqid() . "_" . pathinfo($fileName, PATHINFO_FILENAME)
            ]);

            $url      = $upload['secure_url'];
            $publicId = $upload['public_id'];

            // 💾 Save in pandits collection
            $db->pandits->insertOne([
                "userId"    => $userId,
                "videoUrl"  => $url,
                "publicId"  => $publicId,
                "createdAt" => date("Y-m-d H:i:s")
            ]);

            // 📦 Save in uploads history
            $db->uploads->insertOne([
                "userId"       => $userId,
                "fileName"     => $fileName,
                "originalName" => $originalName,
                "fileType"     => $fileType,
                "category"     => "panditvideo",
                "url"          => $url,
                "publicId"     => $publicId,
                "size"         => $fileSize,
                "createdAt"    => date("Y-m-d H:i:s")
            ]);

            http_response_code(200);
            echo json_encode([
                "apiResponseCode" => 200,
                "apiResponseData" => [
                    "responseCode"    => 200,
                    "responseData"    => ["url" => $url, "publicId" => $publicId],
                    "responseMessage" => "Pandit video uploaded successfully",
                    "responseFrom"    => "uploadFile"
                ],
                "apiResponseFrom"    => "php",
                "apiResponseMessage" => "Pandit video uploaded successfully"
            ]);
            return;
        }

    } catch (Exception $e) {

        http_response_code(500);
        echo json_encode([
            "apiResponseCode" => 500,
            "apiResponseData" => [
                "responseCode"    => 500,
                "responseData"    => null,
                "responseMessage" => $e->getMessage(),
                "responseFrom"    => "uploadFile"
            ],
            "apiResponseFrom"    => "php",
            "apiResponseMessage" => $e->getMessage()
        ]);
    }
}