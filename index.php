<?php
// ✅ CORS — sabse pehle, kuch bhi require se pehle
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// OPTIONS preflight handle karo
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/bootstrap.php';

header("Content-Type: application/json");

include __DIR__ . '/routes/api.php';