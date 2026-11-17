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

    // Supabase Storage Integration
    'supabase' => [
        'url'             => getenv('SUPABASE_URL') ?: '',
        'publishable_key' => getenv('SUPABASE_PUBLISHABLE_KEY') ?: 'sb_publishable_o_rXRF1Qd90XL1SRjas_nA_tAb5S-n4',
        'secret_key'      => getenv('SUPABASE_SECRET_KEY') ?: 'sb_secret_9zmv6Qh893VLAhuXevZ31w_6pBhxfco',
        'bucket'          => getenv('SUPABASE_BUCKET') ?: 'inspeksi',
    ],

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
