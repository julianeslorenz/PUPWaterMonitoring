<?php
require_once 'connection.php';

$type = $_GET['type'] ?? 'daily';
$from = $_GET['from'] ?? '2000-01-01';
$to = $_GET['to'] ?? date('Y-m-d');

switch ($type) {
  case 'weekly':
    $group = "YEAR(logged_at), WEEK(logged_at)";
    $label = "CONCAT('Week ', WEEK(logged_at), ' - ', YEAR(logged_at))";
    break;
  case 'monthly':
    $group = "YEAR(logged_at), MONTH(logged_at)";
    $label = "DATE_FORMAT(logged_at, '%M %Y')";
    break;
  case 'yearly':
    $group = "YEAR(logged_at)";
    $label = "YEAR(logged_at)";
    break;
  default:
    $group = "DATE(logged_at)";
    $label = "DATE(logged_at)";
    break;
}

header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"water_usage_{$type}_{$from}_to_{$to}.csv\"");

$output = fopen("php://output", "w");
fputcsv($output, ['Period', 'Tank Name', 'Total Liters']);

$stmt = $conn->prepare("
  SELECT 
    $label as period_label,
    tank_name,
    SUM(change_liters) as total_liters
  FROM tank_liters_log
  WHERE DATE(logged_at) BETWEEN ? AND ?
  GROUP BY $group, tank_name
  ORDER BY MIN(logged_at)
");
$stmt->execute([$from, $to]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($output, [
    $row['period_label'],
    $row['tank_name'],
    round($row['total_liters'], 2)
  ]);
}

fclose($output);
exit;
