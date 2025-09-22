<?php
require_once 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$tank = $data['tank'];
$prev = $data['previous'];
$current = $data['current'];
$change = $data['change'];
$timestamp = $data['timestamp'];

$stmt = $conn->prepare("INSERT INTO tank_liters_log (tank_name, previous_liters, current_liters, change_liters, logged_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$tank, $prev, $current, $change]);  

echo json_encode(['status' => 'success']);
?>
