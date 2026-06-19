<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "clofs");

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed."]);
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(["error" => "Invalid ID"]);
    exit;
}

$sql = "SELECT secret_question, custom_question FROM found_items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_assoc() ?: []);
$conn->close();
?>
