<?php
include 'mailer.php';

header('Content-Type: application/json');

// DB setup
$conn = new mysqli("localhost", "root", "", "clofs");
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed."]));
}

// Simple similarity calculator
function stringSimilarity($a, $b) {
    similar_text(strtolower($a), strtolower($b), $percent);
    return $percent;
}

// Final match scorer
function scoreMatch($lost, $found) {
    $score = 0;

    $descScore = stringSimilarity($lost['description'], $found['description']);
    $score += ($descScore * 0.35);

    $ansScore = stringSimilarity($lost['ownership_answer'], $found['secret_answer']);
    $score += ($ansScore * 0.35);

    if (strtolower($lost['item_type']) === strtolower($found['item_type'])) {
        $score += 15;
    }

    if (strtolower($lost['location']) === strtolower($found['location'])) {
        $score += 0.05;
    }

    $optionalScore = stringSimilarity($lost['optional_answer'], $found['unique_answer']);
    $score += ($optionalScore * 10);

    return round($score, 2);
}

// ======================== MATCH LOST TO FOUND ========================
if (isset($_GET['lost_id'])) {
    $lost_id = $_GET['lost_id'];
    $lost = $conn->query("SELECT * FROM lost_items WHERE id = $lost_id")->fetch_assoc();
    $foundItems = $conn->query("SELECT * FROM found_items");

    while ($found = $foundItems->fetch_assoc()) {
        $score = scoreMatch($lost, $found);

        // Log if meaningful
        if ($score >= 20) {
            $conn->query("INSERT INTO match_logs (lost_id, found_id, match_score, status) 
                          VALUES ($lost_id, {$found['id']}, $score, 'pending')");

            // 💌 Send email notifications to both parties
            notifyLostAndFoundMatch($lost, $found);

            // Log in server if needed
            error_log("✅ Email sent: Lost ID $lost_id matched with Found ID {$found['id']} | Score: $score");
        }
    }

    echo json_encode(["status" => "done", "matched_with" => $foundItems->num_rows]);

// ======================== MATCH FOUND TO LOST ========================
} elseif (isset($_GET['found_id'])) {
    $found_id = $_GET['found_id'];
    $found = $conn->query("SELECT * FROM found_items WHERE id = $found_id")->fetch_assoc();
    $lostItems = $conn->query("SELECT * FROM lost_items");

    while ($lost = $lostItems->fetch_assoc()) {
        $score = scoreMatch($lost, $found);

        if ($score >= 20) {
            $conn->query("INSERT INTO match_logs (lost_id, found_id, match_score, status) 
                          VALUES ({$lost['id']}, $found_id, $score, 'pending')");

            // 💌 Send email notifications here too
            notifyLostAndFoundMatch($lost, $found);

            error_log("✅ Email sent: Found ID $found_id matched with Lost ID {$lost['id']} | Score: $score");
        }
    }

    echo json_encode(["status" => "done", "matched_with" => $lostItems->num_rows]);

} else {
    echo json_encode(["error" => "No lost_id or found_id provided"]);
}
// Update lost item status
$lostId = $lost['id'];
$conn->query("UPDATE lost_items SET status = 'lost' WHERE id = '$lostId'");

// Update found item status
$foundId = $found['id'];
$conn->query("UPDATE found_items SET status = 'found' WHERE id = '$foundId'");


$conn->close();
?>
