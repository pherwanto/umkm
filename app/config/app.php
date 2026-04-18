<?php
return [
    'app_name' => 'Sistem UMKM Multi-UMKM',
    'base_url' => '/public',
    'timezone' => 'Asia/Jakarta',
    'uploads_dir' => __DIR__ . '/../../public/uploads',
    'sales_tax' => [
        'enabled' => false,
        'percent' => 11,
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-email@gmail.com',
        'password' => 'your-app-password',
        'from_email' => 'your-email@gmail.com',
        'from_name' => 'Sistem UMKM',
        'secure' => 'tls',
    ],
];
