<?php
$conn = new mysqli("localhost", "root", "", "clofs");
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed."]));
}

// Fetch from claims
$claims = mysqli_query($conn, "SELECT id, item_name, status, claim_time AS date_reported FROM claims");

// Fetch from found
$found = mysqli_query($conn, "SELECT id, item_name, status, date_found AS date_reported FROM found_items");

// Fetch from lost
$lost = mysqli_query($conn, "SELECT id, item_name, status, date_lost AS date_reported FROM lost_items");

$historyItems = [];

// Format each result set with unified structure
while ($row = mysqli_fetch_assoc($claims)) {
    $row['source'] = 'Claimed';
    $historyItems[] = $row;
}
while ($row = mysqli_fetch_assoc($found)) {
    $row['source'] = 'Found';
    $historyItems[] = $row;
}
while ($row = mysqli_fetch_assoc($lost)) {
    $row['source'] = 'Lost';
    $historyItems[] = $row;
}

// Sort by date_reported (descending)
usort($historyItems, function($a, $b) {
    return strtotime($b['date_reported']) - strtotime($a['date_reported']);
});

// Add category mapping (simplified based on context)
foreach ($historyItems as &$row) {
    $row['category'] = ($row['source'] == 'Claimed') ? 'Claim' : $row['source'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>History - LOF</title>
 <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #e6f0fa;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    h2 {
      font-size: 24px;
      color: #0056b3;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }
    h2::before {
      content: "📜";
      margin-right: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
   th {
      background-color: #0077b6;
      color: white;
      padding: 10px;
      text-align: left;
    }
    td {
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .status {
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
      display: inline-block;
      border: 1px solid;
    }
    .status-found {
      background-color: #d4edda;
      color: #155724;
      border-color: #c3e6cb;
    }
    .status-claimed {
      background-color: #fff3cd;
      color: #856404;
      border-color: #ffeeba;
    }
    .status-cancelled {
      background-color: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }
   
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="logo">LOF</div>
    <ul class="nav-links">
      <li><a href="dashboard.html">Home</a></li>
      <li><a href="search.html">Search</a></li>
      <li><a href="found.html">Report</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="help.html">Help</a></li>
    </ul>
  </nav>

  <!-- History Table Section -->
  <div class="container">
    <h2>Item History</h2>
    <?php if (empty($historyItems)) { ?>
      <p>No records available.</p>
    <?php } else { ?>
      <table>
        <tr>
          <th>Item</th>
          <th>Category</th>
          <th>Date Reported</th>
          <th>Status</th>
        </tr>
        <?php foreach ($historyItems as $row) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['item_name'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($row['date_reported'] ?? 'N/A'); ?></td>
            <td class="status-<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php } ?>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-column">
        <h3>Contact Us</h3>
        <ul>
          <li>Email: <a href="mailto:support@lofcollege.com">support@lofcollege.com</a></li>
          <li>Phone: +255 712 345 678</li>
          <li>Office: Room 12, Admin Block</li>
          <li>Hours: Mon - Fri | 9AM - 5PM</li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="about.html">About This System</a></li>
          <li><a href="help.html">Help & FAQ</a></li>
          <li><a href="search.html">Search Lost Item</a></li>
          <li><a href="found.html">Report Found Item</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 LOF College. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>