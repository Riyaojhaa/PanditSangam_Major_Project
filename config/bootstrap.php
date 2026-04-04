<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->safeLoad();
