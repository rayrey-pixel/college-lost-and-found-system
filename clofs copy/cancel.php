<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer..php';
require 'PHPMailer/src/SMTP.php';

$host = "localhost";
$user = "root";
$pass = "";
$db = "clofs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize inputs
$reportType = $_POST['report_type'] ?? '';
$verificationCode = $_POST['verification_code'] ?? '';
$email = $_POST['email'] ?? '';

// Validate
if (empty($reportType) || empty($verificationCode) || empty($email)) {
    echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
    exit;
}

$table = ($reportType === 'lost') ? 'lost_items' : 'found_items';

// Check if the report exists
$sql = "SELECT * FROM $table WHERE email = ? AND verification_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Delete the report
    $delete = $conn->prepare("DELETE FROM $table WHERE email = ? AND verification_code = ?");
    $delete->bind_param("ss", $email, $verificationCode);
    $delete->execute();

    echo "<script>alert('Your report has been successfully cancelled.'); window.location.href='dashboard.html';</script>";
} else {
    echo "<script>alert('No matching report found. Please check your email and code.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
