<?php
// Include your database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: ../Loginmainform.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student details from the 'students' table
$student_stmt = $pdo->prepare('SELECT StudNo, course, fname, mname, lname, year_level FROM students WHERE user_id = ?');
$student_stmt->execute([$user_id]);
$student = $student_stmt->fetch();

// Fetch distinct departments from the 'signatory' table
$departments_stmt = $pdo->prepare('SELECT DISTINCT signatory_department FROM signatory');
$departments_stmt->execute();
$departments = $departments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch clearance status from the 'clearance' table
$clearance_stmt = $pdo->prepare('SELECT status FROM clearance WHERE student_id = ?');
$clearance_stmt->execute([$student['StudNo']]);
$clearance_statuses = $clearance_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Clearance Form</title>
    <link rel="stylesheet" href="students_dashboard copy.css"> <!-- Reusing the same CSS file as students_dashboard.php -->
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="students_dashboard.php" class="button">Account Profile</a></li>
            <li><a href="students_clearance.php" class="button">Clearance Form</a></li>
            <li><a href="../index.php" class="button">Log Out</a></li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <h1>Clearance Form</h1>

        

        <!-- Clearance Requirements Table --> 
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid black;
        padding: 8px; /* Add padding for space between columns "CCS code to nasisira pag nilagay ko css eh"*/
        text-align: left;
    }

    th {
        font-weight: bold;
    }
</style>

<table>
    <thead>
        <tr>
            <th>Department</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($departments as $dept) {
            $status = 'Pending'; // Default status
            foreach ($clearance_statuses as $clearance) {
                if ($clearance['signatory_department'] == $dept['signatory_department']) {
                    $status = $clearance['status'];
                    break;
                }   
            }
            echo "<tr>
                    <td>{$dept['signatory_department']}</td>
                    <td>{$status}</td>
                    <td></td> <!-- Add Notes column here if you have data -->
                  </tr>";
        }
        ?>
    </tbody>
</table>

        </form>
    </div>
</body>
</html>
