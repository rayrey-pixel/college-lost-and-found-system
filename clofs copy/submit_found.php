<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer..php';
require 'PHPMailer/src/SMTP.php';

// Connect to the database
$conn = new mysqli("localhost", "root", "", "clofs");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize and collect form inputs
$item_type = $_POST['item-type'];
$item_name = $_POST['item-name'];
$description = $_POST['description'];
$location = $_POST['location'];
$date_found = $_POST['date-found'];
$secret_question = $_POST['secret-question'];
$custom_question = isset($_POST['custom-question']) ? $_POST['custom-question'] : null;
$secret_answer = $_POST['secret-answer'];
$unique_answer = $_POST['unique-answer'];
$verification_code = rand(100000, 999999); 
$email = $_POST['email'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';

// Handle optional photo upload
$photo_path = "";
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir);
    }
    $filename = basename($_FILES["photo"]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $filename;
    move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
    $photo_path = $targetFile;
}

// Insert into database
$sql = "INSERT INTO found_items (item_type, item_name, description, location, date_found, photo_path, secret_question, secret_answer, custom_question, unique_answer,  verification_code, email, full_name, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssss", $item_type, $item_name, $description, $location, $date_found, $photo_path, $secret_question, $secret_answer, $custom_question, $unique_answer, $verification_code,  $email, $full_name, $phone);

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
    $mail->AltBody = "Hi $fullName,\n\nYour found item report was successfully submitted.\nVerification code: $verification_code\n\nKeep this safe.\n\n- CLOFS System";

    $mail->send();
} catch (Exception $e) {
    error_log("Confirmation Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
}

if ($stmt->execute()) {
    // Get the ID of the newly inserted found item
    $found_id = $conn->insert_id;

    // Call the match_score.php script
    file_get_contents("http://localhost/CLOFS/match_score.php?found_id=$found_id");

    echo "<script>alert('Found item report submitted successfully!'); window.location.href='dashboard.html';</script>";
}

$stmt->close();
$conn->close();
?>
