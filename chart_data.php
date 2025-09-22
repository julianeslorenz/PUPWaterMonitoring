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

$query = "
  SELECT 
    $label AS label,
    tank_name,
    SUM(CASE WHEN current_liters = 0 THEN change_liters ELSE 0 END) AS total_liters
  FROM tank_liters_log
  WHERE DATE(logged_at) BETWEEN ? AND ?
  GROUP BY $group, tank_name
  ORDER BY MIN(logged_at)
";


$stmt = $conn->prepare($query);
$stmt->execute([$from, $to]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize by tank
$result = [];
foreach ($data as $row) {
  $result[$row['tank_name']]['labels'][] = $row['label'];
  $result[$row['tank_name']]['data'][] = round($row['total_liters'], 2);
}

echo json_encode($result);
?>
