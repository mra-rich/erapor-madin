<?php
require 'koneksi.php';
session_start();
require_once 'csrf.php';
$token = generate_csrf_token();
$payload = [
    'csrf_token' => $token,
    'import_data' => [
        [
            'nip' => '1234567890' . rand(1, 1000), // Randomize nip
            'nama_lengkap' => 'Test Guru',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'no_hp' => '08123456789'
        ]
    ]
];
file_put_contents('test_payload.json', json_encode($payload));
