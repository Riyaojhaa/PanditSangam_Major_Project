<?php
require_once __DIR__ . '/config/bootstrap.php';

header("Content-Type: application/json");

include __DIR__ . '/routes/api.php';