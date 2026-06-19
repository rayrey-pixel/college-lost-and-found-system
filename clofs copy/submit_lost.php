<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer..php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

$host = "localhost";
$user = "root";
$pass = "";
$db = "clofs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize inputs
$itemName    = $_POST['item_name'] ?? '';
$itemType    = $_POST['item_type'] ?? '';
$description = $_POST['description'] ?? '';
$location    = $_POST['location'] ?? '';
$dateLost    = $_POST['date_lost'] ?? '';
$fullName    = $_POST['full_name'] ?? '';
$email       = $_POST['email'] ?? '';
$phone       = $_POST['phone'] ?? '';
$verification_code = rand(100000, 999999); 

$sql = "INSERT INTO lost_items (item_type, item_name, description, location, date_lost, full_name, email, phone, verification_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $itemType, $itemName, $description, $location, $dateLost, $fullName, $email, $phone, $verification_code);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rehemarupia@gmail.com';
        $mail->Password   = 'cjequrwtvuzwygad'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

    $mail->setFrom('no-reply@clofs.local', 'CLOFS System');
    $mail->addAddress($email, $fullName);

    $mail->isHTML(true);
    $mail->Subject = 'Your CLOFS Verification Code';
    $mail->Body    = "
        <h3>Hi {$fullName},</h3>
        <p>Your report was successfully submitted to the Campus Lost & Found System.</p>
        <p><strong>Your verification code is: <span style='font-size: 20px;'>$verification_code</span></strong></p>
        <p>Keep this code safe in case you wish to cancel your report later.</p>
        <br>
        <p>Thank you,<br>CLOFS System</p>
    ";
    $mail->AltBody = "Hi $fullName,\n\nYour lost item report was successfully submitted.\nVerification code: $verification_code\n\nKeep this safe.\n\n- CLOFS System";

    $mail->send();
} catch (Exception $e) {
    error_log("Confirmation Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
}


if ($stmt->execute()) {
    $lost_id = $conn->insert_id;

    // Optional: Trigger matching logic
    file_get_contents("http://localhost/CLOFS/match_score.php?lost_id=$lost_id");

    echo "<script>alert('Lost item report submitted successfully!'); window.location.href='dashboard.html';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
<script type="text/javascript">
    // Optionally add this in the frontend to prevent resubmission alerts
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

</script>