<?php
/**
 * Konfigurasi Aplikasi
 * Sistem Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor
 * Developer: Yayasan Ambadar
 */
return [
    'name'        => 'Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor',
    'short_name'  => 'Inspeksi Al-Wildan 8',
    'institution' => 'Al-Wildan Islamic School 8 Kemang Bogor',
    'developer'   => 'Yayasan Ambadar',
    'url'         => getenv('APP_URL') ?: 'http://localhost:8000',
    'debug'       => (bool)(getenv('APP_DEBUG') ?: true),
    'timezone'    => 'Asia/Jakarta',

    // Scoring (sinkron dengan standar kebersihan)
    'score' => [
        'bersih'        => 100,
        'kurang_bersih' => 70,
        'kotor'         => 30,
        'threshold_alert' => 50,
    ],

    // Upload
    'upload' => [
        'max_size'   => 2048, // KB
        'allowed'    => ['jpg', 'jpeg', 'png', 'webp'],
        'path'       => PUBLIC_PATH . '/uploads',
    ],

    // Session
    'session_lifetime' => 7200, // detik

    // Pagination
    'per_page' => 15,
];
