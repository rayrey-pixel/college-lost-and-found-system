<?php

include 'mailer.php';

if (!function_exists('matchClaimToFound')) {

function matchClaimToFound($conn, $claimId) {
    echo "<pre>Running PHP-powered match logic for Claim ID: $claimId</pre>";

    // 1. Fetch claim details
    $claimSql = "SELECT * FROM claims WHERE id = ?";
    $stmt = $conn->prepare($claimSql);
    $stmt->bind_param("i", $claimId);
    $stmt->execute();
    $claimResult = $stmt->get_result();
    $claim = $claimResult->fetch_assoc();
    $stmt->close();

    if (!$claim) {
        echo "❌ Claim not found.\n";
        return false;
    }

    // 2. Fetch all found items
    $foundSql = "SELECT * FROM found_items";
    $foundResult = $conn->query($foundSql);

    $matched = false;

    while ($found = $foundResult->fetch_assoc()) {
        $score = 0;

        // Match: item name
        similar_text(strtolower($claim['item_name']), strtolower($found['item_name']), $itemPercent);
        $score += round($itemPercent * 0.2);

        // Match: description vs more_details
        similar_text(strtolower($claim['more_details']), strtolower($found['description']), $descPercent);
        $score += round($descPercent * 0.4);

        // Match: ownership answer
        similar_text(strtolower($claim['ownership_answer']), strtolower($found['secret_answer']), $secretPercent);
        $score += round($secretPercent * 0.3);

        // Optional question
        if (!empty($claim['optional_answer']) && !empty($found['unique_answer'])) {
            similar_text(strtolower($claim['optional_answer']), strtolower($found['unique_answer']), $optionalPercent);
            $score += round($optionalPercent * 0.1);
        }

        // ✅ If match score is decent
        if ($score >= 20) {
            $lost_id = $claim['item_id'];
            $found_id = $found['id'];
            $status = 'pending';

            $insertLog = $conn->prepare("INSERT INTO match_logs (lost_id, found_id, claim_id, match_score, status) VALUES (?, ?, ?, ?, ?)");
            $insertLog->bind_param("iiiis", $lost_id, $found_id, $claimId, $score, $status);
            $insertLog->execute();

            // 💌 Send email to claimer & founder
            notifyClaimMatch($claim, $found);

            // Optional: log to server
            error_log("✅ Email sent for Claim ID $claimId matched with Found ID $found_id | Score: $score");

            $matched = true;
        }
    }

    // Final response
    if ($matched) {
        echo "<script>alert('Claim submitted ✅\\nStrong match found, logged successfully. Please wait for email notification'); window.location.href='dashboard.html';</script>";
    } else {
        echo "<script>alert('Claim submitted ✅\\nNo strong match found.'); window.location.href='dashboard.html';</script>";
    }
// Update claim status
$claimId = $claim['id'];
$conn->query("UPDATE claims SET status = 'Matched' WHERE id = '$claimId'");

// Update found item status
$foundId = $found['id'];
$conn->query("UPDATE found_items SET status = 'Claimed' WHERE id = '$foundId'");

    return $matched;
}
}
?>
