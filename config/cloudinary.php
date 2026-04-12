<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;

function getCloudinary(){
    return new Cloudinary([
        'cloud' => [
            'cloud_name' => $_ENV['CLOUD_NAME'],
            'api_key'    => $_ENV['API_KEY'],
            'api_secret' => $_ENV['API_SECRET']
        ]
    ]);
}