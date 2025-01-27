<?php
// Include your database connection
require_once '../Database/clearancedb.php';
session_start();

// Validate input
if (!isset($_GET['student_id'], $_GET['cpid'])) {
    die('Invalid request.');
}

$student_id = $_GET['student_id'];
$cpid = $_GET['cpid'];

// Fetch student information and clearance details
$stmt = $pdo->prepare('
    SELECT s.StudNo, s.fname, s.mname, s.lname, s.course, cp.school_year, cp.semester, cp.clearancetype
    FROM students s
    JOIN clearance c ON s.student_id = c.student_id
    JOIN clearance_period cp ON c.Cpid = cp.Cpid
    WHERE s.student_id = ? AND cp.Cpid = ?
');
$stmt->execute([$student_id, $cpid]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('Student or clearance period not found.');
}

// Fetch clearance details for departments
$details_stmt = $pdo->prepare('
    SELECT d.signatory_department, cd.deptstatus AS status, cd.lackingreq AS notes
    FROM clearance_details cd
    JOIN signatory d ON cd.signatory_id = d.signatory_id
    WHERE cd.student_id = ? AND cd.Cpid = ?
');
$details_stmt->execute([$student_id, $cpid]);
$clearance_details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Clearance Form</title>
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Signatory Dashboard</h2>
        <ul>
            <li><a href="Signatory_Dashboard.php" class="button">Signatory Account</a></li>
            <li><a href="Clearancedashboard.php" class="button">Clearance Dashboard</a></li>
            <li><a href="Signatory_clearanceforms.php" class="button">Clearance Forms</a></li>
            <li><a href="../logout.php" class="button">Log Out</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="clearance-form-card">
            <h1>Student Clearance Form</h1>
        </div>

        <!-- Student Details Card -->
        <div class="card">
            <h2>Student Information</h2>
            <p><strong>Student No.:</strong> <?php echo htmlspecialchars($student['StudNo']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['fname'] . ' ' . $student['mname'] . ' ' . $student['lname']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
            <p><strong>School Year:</strong> <?php echo htmlspecialchars($student['school_year']); ?></p>
            <p><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester']); ?></p>
            <p><strong>Clearance Type:</strong> <?php echo htmlspecialchars($student['clearancetype']); ?></p>
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
                <?php foreach ($clearance_details as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['signatory_department']); ?></td>
                        <td><?php echo htmlspecialchars($detail['status']); ?></td>
                        <td><?php echo htmlspecialchars($detail['notes']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
