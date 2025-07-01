<?php
// update_gas_data.php
// File ini akan menerima data dari MQTT broker via webhook atau manual POST
// dan menyimpannya ke file teks untuk dibaca oleh index.php

// Pastikan hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    // echo "Metode request tidak diizinkan."; // Hapus atau komentari ini
    exit();
}

// Baca raw POST body
$input = file_get_contents('php://input');
$data = json_decode($input, true); // Asumsikan data dikirim dalam format JSON

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    // echo "Format JSON tidak valid."; // Hapus atau komentari ini
    exit();
}

$gasValue = "N/A";
$alarmStatus = "IDLE";

// Sesuaikan ini dengan format JSON yang dikirim oleh webhook broker Anda
// Atau jika Anda mengirim dari aplikasi lain, pastikan key-nya cocok.
// Untuk demonstrasi, kita asumsikan webhook mengirim { "topic": "...", "payload": "..." }
// Dan payload adalah string "Suhu: XXXC" atau "BAHAYA GAS! Nilai: XXX"
if (isset($data['topic']) && isset($data['payload'])) {
    $topic = $data['topic'];
    $payload = $data['payload'];

    // Jika ini adalah data gas sensor
    if ($topic === 'esp32/sensor/gas') {
        if (strpos($payload, 'Suhu:') !== false) {
            $gasValue = trim(str_replace('Suhu:', '', $payload)); 
        } elseif (strpos($payload, 'Gas Value:') !== false) {
             $gasValue = trim(str_replace('Gas Value:', '', $payload)); 
        } elseif (strpos($payload, 'BAHAYA GAS! Nilai:') !== false) {
            $gasValue = trim(str_replace('BAHAYA GAS! Nilai:', '', $payload));
        } else {
            $gasValue = $payload; 
        }
    }
    // Jika ini adalah status alarm
    elseif ($topic === 'esp32/status/alarm') {
        $alarmStatus = $payload; 
    }
} else {
    // Jika tidak ada data JSON atau format tidak sesuai, coba ambil dari POST biasa
    $gasValue = isset($_POST['gas_value']) ? $_POST['gas_value'] : "N/A";
    $alarmStatus = isset($_POST['alarm_status']) ? $_POST['alarm_status'] : "IDLE";
}


$timestamp = date('Y-m-d H:i:s');
$dataFile = 'last_gas_data.txt';

// Simpan data ke file
// Hati-hati: Di lingkungan produksi, gunakan database!
$content = "GasValue:" . $gasValue . "\n";
$content .= "Timestamp:" . $timestamp . "\n";
$content .= "AlarmStatus:" . $alarmStatus . "\n";
file_put_contents($dataFile, $content);

http_response_code(200); // OK
// echo "Data diterima dan disimpan."; // Hapus atau komentari ini
?>