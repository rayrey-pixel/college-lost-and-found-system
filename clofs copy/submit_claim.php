<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "clofs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Grab and sanitize inputs
$item_id = $_POST['item_id'] ?? null;
$claimed_item = $_POST['claimed-item'] ?? '';
$full_name = $_POST['full-name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$ownership_answer = $_POST['ownership-answer'] ?? '';
$optional_answer = $_POST['optional-answer'] ?? null;
$more_details = $_POST['more-details'] ?? '';

// Basic validation
if (!$item_id || !$claimed_item || !$full_name || !$email || !$phone || !$ownership_answer) {
    error_log("Validation failed: Missing required fields");
    echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
    exit;
}

// Insert into claims table with claim_time
$sql = "INSERT INTO claims 
        (item_id, item_name, full_name, email, phone, ownership_answer, optional_answer, more_details) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        error_log("SQL: $sql");

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("isssssss", $item_id, $claimed_item, $full_name, $email, $phone, $ownership_answer, $optional_answer, $more_details);

if ($stmt->execute()) {
    $claimId = $stmt->insert_id;
    include_once 'match_claim.php';
    matchClaimToFound($conn, $claimId);
} else {
    error_log("Execute failed: " . $stmt->error . " with data: item_id=$item_id, claimed_item=$claimed_item");
    echo "<script>alert('Error saving your claim. Please try again later.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
 ?>
