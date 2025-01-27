<?php
// Include your database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student details from the 'students' table
$student_stmt = $pdo->prepare('SELECT student_id, StudNo, course, fname, mname, lname, year_level FROM students WHERE user_id = ?');
$student_stmt->execute([$user_id]);
$student = $student_stmt->fetch();
$student_id = $student['student_id'];

// Fetch clearance periods where the student has a record in `clearance`
$clearance_periods = $pdo->prepare('
    SELECT cp.*
    FROM clearance_period cp
    JOIN clearance c ON cp.Cpid = c.Cpid
    WHERE c.student_id = ?
');
$clearance_periods->execute([$student_id]);
$clearance_periods = $clearance_periods->fetchAll(PDO::FETCH_ASSOC);

// Get the selected `Cpid` (clearance period ID) from the GET request
$selected_cpid = $_GET['Cpid'] ?? null;

// Ensure `Cpid` is valid and corresponds to a clearance period
if (!$selected_cpid || !array_filter($clearance_periods, fn($p) => $p['Cpid'] == $selected_cpid)) {
    die("Invalid or missing clearance period.");
}

// Fetch all departments and the lacking requirements for the student from `clearance_details` based on `Cpid`
$stmt = $pdo->prepare(
    'SELECT s.signatory_department, 
            sd.deptstatus AS status, 
            sd.lackingreq AS notes
     FROM signatory s
     LEFT JOIN clearance_details sd ON s.signatory_id = sd.signatory_id 
     AND sd.student_id = ? 
     AND sd.Cpid = ?
     ORDER BY s.signatory_id'
);
$stmt->execute([$student_id, $selected_cpid]);
$department_statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if all statuses are "Signed"
$all_signed = array_reduce($department_statuses, function ($carry, $dept) {
    return $carry && ($dept['status'] === 'Signed');
}, true);

// Update clearance status in the `clearance` table
$clearance_status = $all_signed ? 'Complete' : 'Pending';
$update_clearance_stmt = $pdo->prepare('UPDATE clearance SET status = ? WHERE student_id = ? AND Cpid = ?');
$update_clearance_stmt->execute([$clearance_status, $student_id, $selected_cpid]);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Student Dashboard</title>
    <style>
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Set off-white background for the entire body */
    body {
        font-family: 'Arial', sans-serif;
        display: flex;
        min-height: 100vh;
        background-color: gold;
    }

    /* Sidebar Styles */
    .sidebar {
        width: 250px;
        background-color: #8b0000; /* Dark red for signatory theme */
        color: white;
        padding: 30px 20px;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        height: 100vh; /* Full height of the viewport */
        overflow-y: auto; /* Allows sidebar scrolling */
        z-index: 1000; /* Ensure the sidebar stays on top */
    }

    .logo {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 40px;
    }

    .sidebar ul {
        list-style: none;
    }

    .sidebar ul li {
        margin-bottom: 20px;
    }

    .sidebar ul li a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 10px;
        font-size: 18px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .sidebar ul li a.active,
    .sidebar ul li a:hover {
        background-color: #a52a2a; /* Darker red for hover */
    }

    /* Main Content Styles */
    .main-content {
        margin-left: 270px;
        padding: 40px;
        background-color: #fefefe;
        height: 100vh;
        overflow-y: auto;
        width: calc(100% - 270px);
    }

    /* Clearance Form Card Styling */
    .clearance-form-card {
        width: 100%; /* Match the table width */
        max-width: 100%;
        padding: 20px;
        background: maroon;
        border-radius: 30px;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 30px 50px -12px inset, rgba(0, 0, 0, 0.3) 0px 18px 26px -18px inset;
        color: white;
        text-align: center;
        margin-bottom: 20px;
    }

    .clearance-form-card h1 {
        font-size: 24px;
        color: white;
    }

    /* Additional Card styling */
    .card {
        width: 100%;
        max-width: 600px;
        border-radius: 20px;
        background: maroon;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 30px 50px -12px inset, rgba(0, 0, 0, 0.3) 0px 18px 26px -18px inset;
        padding: 20px;
        margin: 20px 0;
        color: white;
        text-align: left;
    }

    .card h2 {
        font-size: 20px;
        margin: 0 0 10px;
        color: white;
    }

    .card p {
        margin: 8px 0;
        font-size: 16px;
        color: white;
    }

    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 20px;
        background: #f8f8f8;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 30px 50px -12px inset, rgba(0, 0, 0, 0.3) 0px 18px 26px -18px inset;
        overflow: hidden;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid rgba(0, 0, 0, 0.1);
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #d3d3d3;
        font-weight: bold;
    }

    table thead th {
        padding: 12px;
        background-color: #981105;
        color: #fff;
        text-align: left;
        border-bottom: 1px solid #555;
    }

    table tbody td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        color: #333;
    }

    /* Back button styling */
    .back-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 10px 20px;
        background-color: #8b0000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .back-button:hover {
        background-color: #a52a2a;
    }
    /* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Enable scrolling if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* Center the modal */
    padding: 20px;
    border: 1px solid #888;
    width: 50%; /* Width of the modal */
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* QR Button Styling */
.qr-button {
    background-color: #b31d1d;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}

.qr-button:hover {
    background-color: #a01919;
}

    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Student Dashboard</h2>
        <ul>
            <li>
                <a href="students_dashboard.php">
                    <i class="fas fa-user"></i> Account Profile
                </a>
            </li>
            <li>
                <a href="students_clearanceform.php">
                    <i class="fas fa-file-alt"></i> Clearance Form
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="clearance-form-card">
            <h1>Clearance Form</h1>
        </div>

        <!-- Student Details Card -->
        <div class="card">
            <h2>Student Information</h2>
            <p><strong>Student No.:</strong> <?php echo htmlspecialchars($student['StudNo']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['fname'] . ' ' . $student['mname'] . ' ' . $student['lname']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
        </div>

        <!-- Select Clearance Period -->
        <form method="GET" action="students_detailsform.php" style="display: flex; align-items: center; gap: 10px;">
    <label for="clearance-period">Select Clearance Period:</label>
    <select name="Cpid" id="clearance-period" onchange="this.form.submit()">
        <?php foreach ($clearance_periods as $period): ?>
            <option value="<?php echo $period['Cpid']; ?>" <?php echo ($period['Cpid'] == $selected_cpid) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($period['school_year'] . ' - ' . $period['semester'] . ' (' . $period['clearancetype'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
    <!-- Show QR Code Button -->
    <button type="button" class="qr-button" onclick="showQRCode()">Show QR Code</button>
</form>

<!-- Modal for QR Code -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p><div id="qrcode"></div></p>
    </div>
</div>
                    
        <!-- Clearance Requirements Table -->
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($department_statuses as $dept): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dept['signatory_department']); ?></td>
                        <td><?php echo htmlspecialchars($dept['status'] ?? 'Pending'); ?></td>
                        <td><?php echo htmlspecialchars($dept['notes'] ?? ''); ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<script src="./qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById("qrcode"), JSON.stringify({user_id:"<?=$user_id?>",cpid:"<?=$selected_cpid?>"}));

    function showQRCode() {
        document.getElementById('qrModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('qrModal').style.display = 'none';
    }

    // Close modal if clicked outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('qrModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

        <!-- Back Button -->
        <a href="students_clearanceform.php" class="back-button">Back</a>
    </div>
</body>
</html>
