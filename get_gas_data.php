<?php
header('Content-Type: application/json'); // Beri tahu browser bahwa ini adalah JSON

$dataFile = 'last_gas_data.txt';
$gasValue = "N/A";
$lastUpdateTime = "N/A";
$alarmStatus = "UNKNOWN";

if (file_exists($dataFile)) {
    $data = file_get_contents($dataFile);
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        if (strpos($line, 'GasValue:') !== false) {
            $gasValue = trim(str_replace('GasValue:', '', $line));
        } elseif (strpos($line, 'Timestamp:') !== false) {
            $lastUpdateTime = trim(str_replace('Timestamp:', '', $line));
        } elseif (strpos($line, 'AlarmStatus:') !== false) {
            $alarmStatus = trim(str_replace('AlarmStatus:', '', $line));
        }
    }
}

$response = [
    'gasValue' => $gasValue,
    'timestamp' => $lastUpdateTime,
    'alarmStatus' => $alarmStatus
];

echo json_encode($response);
?>