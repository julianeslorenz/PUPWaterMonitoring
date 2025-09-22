<?php
// Database connection
$host = 'localhost'; // or your actual host
$db   = 'pup_water_monitoring'; // change to your database name
$user = 'root';       // change to your DB user
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Load JSON data
$data = json_decode(file_get_contents('data.json'), true);
$tank1 = $data['tank1'];
$tank2 = $data['tank2'];

function processReading($raw) {
    if ($raw <= 6) return 0;
    if ($raw >= 79) return 100;
    return $raw;
}

$tank1_percent = processReading($tank1);
$tank2_percent = processReading($tank2);

// Read previous notification state
$notifyFile = 'last_notification.json';
$lastNotification = file_exists($notifyFile) ? json_decode(file_get_contents($notifyFile), true) : ['tank1' => '', 'tank2' => ''];

// Include PHPMailer
require __DIR__ . '/../water/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../water/PHPMailer/src/SMTP.php';
require __DIR__ . '/../water/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Gmail App Credentials
$appPassword = 'vwvytmkfzubhbsae';
$from = 'pupaquamonitoring@gmail.com';

// Fetch all active recipients
$stmt = $pdo->query("SELECT email FROM email_recipients");
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Send to all recipients
function sendToAll($emails, $subject, $body, $from, $appPassword) {
    foreach ($emails as $to) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $from;
            $mail->Password   = $appPassword;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($from, 'Water Tank Monitor');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        } catch (Exception $e) {
            error_log("Mail error to $to: " . $mail->ErrorInfo);
        }
    }
}

// Track notification status
$updatedNotification = $lastNotification;

// Tank logic
foreach (['tank1', 'tank2'] as $tank) {
    $percent = ($tank === 'tank1') ? $tank1_percent : $tank2_percent;
    $state = $lastNotification[$tank];

    if ($percent >= 90 && $state !== 'high') {
        $msg = strtoupper($tank) . " is at {$percent}%. Almost full. Please turn OFF the tank.";
        sendToAll($emails, "High Level Alert: " . strtoupper($tank), $msg, $from, $appPassword);
        $updatedNotification[$tank] = 'high';
    } elseif ($percent <= 20 && $state !== 'low') {
        $msg = strtoupper($tank) . " is at {$percent}%. Low level. You may turn ON the tank.";
        sendToAll($emails, "Low Level Alert: " . strtoupper($tank), $msg, $from, $appPassword);
        $updatedNotification[$tank] = 'low';
    } elseif ($percent > 20 && $percent < 90) {
        $updatedNotification[$tank] = 'normal';
    }
}

// Save notification status
file_put_contents($notifyFile, json_encode($updatedNotification));

// Add this for debugging:
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'tank1_percent' => $tank1_percent,
    'tank2_percent' => $tank2_percent,
    'notification' => $updatedNotification,
    'emails' => $emails
]);
?>
