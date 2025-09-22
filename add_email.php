<?php 
$conn = new mysqli("localhost", "root", "", "pup_water_monitoring");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['add_email_submit'])) {
    $email = trim($conn->real_escape_string($_POST['email']));

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $checkQuery = "SELECT * FROM email_recipients WHERE email = '$email'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows > 0) {
            // Email already exists
            header("Location: dashboard.php?exists=1#settings");
            exit();
        } else {
            // Insert new email
            $sql = "INSERT INTO email_recipients (email) VALUES ('$email')";
            if ($conn->query($sql) === TRUE) {
                header("Location: dashboard.php?success=1#settings");
                exit();
            } else {
                header("Location: dashboard.php?success=0#settings");
                exit();
            }
        }
    } else {
        header("Location: dashboard.php?success=0");
        exit();
    }
}

$conn->close();
?>
