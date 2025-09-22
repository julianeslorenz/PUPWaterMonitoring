<?php
// Kunin ang GET parameters
$tank1 = isset($_GET['tank1']) ? intval($_GET['tank1']) : null;
$tank2 = isset($_GET['tank2']) ? intval($_GET['tank2']) : null;

if ($tank1 === null || $tank2 === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

// Data array
$data = [
    "tank1" => $tank1,
    "tank2" => $tank2,
    "timestamp" => time()
];

// Isulat sa data.json
file_put_contents("data.json", json_encode($data));

echo json_encode(["status" => "success", "message" => "Data updated"]);
?>
