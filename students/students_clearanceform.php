<?php
// Include the database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the student's `student_id` from the `students` table based on `user_id`
$student_stmt = $pdo->prepare('SELECT student_id FROM students WHERE user_id = ?');
$student_stmt->execute([$user_id]);
$student_id = $student_stmt->fetchColumn();

if (!$student_id) {
    die("Student ID not found for this user.");
}

// Fetch available clearance periods
$clearance_periods = $pdo->query('SELECT school_year, semester, startdate, enddate, clearancetype FROM clearance_period')->fetchAll(PDO::FETCH_ASSOC);

// Get today's date for filtering
$current_date = date('Y-m-d');

// Fetch student's current clearance records, but filter based on start date
$student_clearances = $pdo->prepare('
    SELECT cp.school_year, cp.semester, cp.startdate, cp.enddate, cp.clearancetype, c.status, c.Cpid
    FROM clearance c
    JOIN clearance_period cp ON c.Cpid = cp.Cpid
    WHERE c.student_id = ? AND cp.startdate <= ?
');
$student_clearances->execute([$student_id, $current_date]);
$my_clearances = $student_clearances->fetchAll(PDO::FETCH_ASSOC);

// Initialize message variable for feedback
$message = "";

// Check and update status dynamically (optional step for clarity)
foreach ($my_clearances as $clearance) {
    $Cpid = $clearance['Cpid'];
    
    // Fetch departments for the clearance period
    $dept_stmt = $pdo->prepare('
        SELECT sd.deptstatus 
        FROM clearance_details sd
        WHERE sd.student_id = ? AND sd.Cpid = ?
    ');
    $dept_stmt->execute([$student_id, $Cpid]);
    $dept_statuses = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if all departments are cleared
    $all_cleared = !empty($dept_statuses) && array_reduce($dept_statuses, function ($carry, $status) {
        return $carry && ($status === 'Cleared');
    }, true);

    // Update clearance status if needed
    $updated_status = $all_cleared ? 'Complete' : 'Pending';
    $update_stmt = $pdo->prepare('UPDATE clearance SET status = ? WHERE student_id = ? AND Cpid = ?');
    $update_stmt->execute([$updated_status, $student_id, $Cpid]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Clearance Form</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="students_clearanceform.css">
</head>
<body>
    <div class="sidebar">
    <h3 class="logo">Student Dashboard</h3>
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
        <h1>Available Clearance Forms</h1>
        <div class="available-clearance">
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Clearance Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearance_periods as $period): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($period['school_year']); ?></td>
                            <td><?php echo htmlspecialchars($period['semester']); ?></td>
                            <td><?php echo htmlspecialchars($period['startdate']); ?></td>
                            <td><?php echo htmlspecialchars($period['enddate']); ?></td>
                            <td><?php echo htmlspecialchars($period['clearancetype']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h1>My Student Clearance Form</h1>
        <div class="student-clearance">
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Clearance Type</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_clearances)): ?>
                        <?php foreach ($my_clearances as $clearance): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($clearance['school_year']); ?></td>
                                <td><?php echo htmlspecialchars($clearance['semester']); ?></td>
                                <td><?php echo htmlspecialchars($clearance['startdate']); ?></td>
                                <td><?php echo htmlspecialchars($clearance['enddate']); ?></td>
                                <td><?php echo htmlspecialchars($clearance['clearancetype']); ?></td>
                                <td><?php echo htmlspecialchars($clearance['status']); ?></td>
                                <td>
                                    <form action="students_detailsform.php" method="GET">
                                        <input type="hidden" name="Cpid" value="<?php echo htmlspecialchars($clearance['Cpid']); ?>">
                                        <button type="submit">View</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No clearance forms available for the current date.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

