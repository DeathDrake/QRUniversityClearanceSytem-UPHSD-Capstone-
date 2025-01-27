<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the logged-in user_id from session
$user_id = $_SESSION['user_id'];

$statement = $pdo->prepare('SELECT email FROM admin WHERE user_id = ?');
$statement->execute([$user_id]);
$email = $statement->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR</title>
    <link rel="stylesheet" href="Admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
</head>
<body>
<div class="sidebar">
    <img src="images/perpetualsmallicon.png" alt="Perpetual Logo" class="logo-image">
    <ul>
        <li><a href="Admin_dashboard.php" class="button"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="Admin_Create_Account.php" class="button"><i class="fas fa-user-plus"></i> Create Account</a></li>
        <li><a href="Create_Clearanceform.php" class="button"><i class="fas fa-file-alt"></i> Create ClearanceForm</a></li>
        <li><a href="#" class="button" onclick="openQRModal()"><i class="fas fa-qrcode"></i> Scan QR</a></li>
        <li><a href="Graph.php" class="button"><i class="fas fa-chart-bar"></i> Graph</a></li>
    </ul>
    <div class="sidebar-bottom">
        <a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');" class="button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        <p>Logged in as: <?php echo htmlspecialchars($email); ?></p>
    </div>
</div>

<div class="main-content">
    <!-- Button to Open Modal -->
    <button class="button" onclick="openQRModal()">Start QR Scan</button>
    
    <!-- Modal for Scan QR -->
    <div id="qrScanModal" class="modal" style="display:none; justify-content:center; align-items:center;">
        <div class="modal-content">
            <span class="close" onclick="closeQRModal()">&times;</span>
            <h3>Scan QR</h3>
            <p><div id="reader"></div></p>
        </div>
    </div>
</div>

<script>
let lastScan = null;

function onScanSuccess(decodedText, decodedResult) {
    const data = JSON.parse(decodedText);
    if (!data.user_id || !data.cpid) return;
    if (lastScan?.user_id == data.user_id && lastScan?.cpid == data.cpid) return;
    lastScan = data;
    window.open(`./Admin_showresults.php?user_id=${data.user_id}&cpid=${data.cpid}`, '_blank');
}

function onScanFailure(error) {
    console.warn(`QR scan failed: ${error}`);
}

function openQRModal() {
    document.getElementById('qrScanModal').style.display = 'flex'; // Show modal
    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function closeQRModal() {
    document.getElementById('qrScanModal').style.display = 'none'; // Hide modal
    lastScan = null;
}

// Close modal when clicking outside the modal content
window.onclick = function (event) {
    const modal = document.getElementById('qrScanModal');
    if (event.target === modal) {
        closeQRModal();
    }
};
</script>
</body>
</html>
