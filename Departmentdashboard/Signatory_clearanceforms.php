<?php
// Include the database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in and is a Signatory Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch available clearance periods
$clearance_periods = $pdo->query('SELECT school_year, semester, startdate, enddate, clearancetype, Cpid FROM clearance_period')->fetchAll(PDO::FETCH_ASSOC);

// Fetch current clearance forms with students
$current_clearances = $pdo->query('
    SELECT cp.school_year, cp.semester, cp.startdate, cp.enddate, cp.clearancetype, COUNT(c.clearance_id) AS student_count, cp.Cpid
    FROM clearance c
    JOIN clearance_period cp ON c.Cpid = cp.Cpid
    GROUP BY c.Cpid
')->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Clearance Forms</title>
    <link rel="stylesheet" href="Signatory_clearanceforms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Signatory Dashboard</h2>
        <ul>
            
            <li>
                <a href="Signatory_Dashboard.php">
                    <i class="fas fa-user"></i> Signatory Account
                </a>
            </li>
            <li>
                <a href="Clearancedashboard.php">
                    <i class="fas fa-file-alt"></i> Clearance Dashboard
                </a>
            </li>
            <li>
                <a href="Signatory_clearanceforms.php">
                    <i class="fas fa-file-alt"></i> Clearance Forms
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
                        <th>Clearance Type</th> <!-- Added ClearanceType -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearance_periods as $period): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($period['school_year']); ?></td>
                            <td><?php echo htmlspecialchars($period['semester']); ?></td>
                            <td><?php echo htmlspecialchars($period['startdate']); ?></td>
                            <td><?php echo htmlspecialchars($period['enddate']); ?></td>
                            <td><?php echo htmlspecialchars($period['clearancetype']); ?></td> <!-- Display ClearanceType -->
                            <td>
                                <form action="Studentslist.php" method="GET">
                                    <input type="hidden" name="cpid" value="<?php echo htmlspecialchars($period['Cpid']); ?>">
                                    <button type="submit">Add</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h1>Current Clearance Forms</h1>
        <div class="current-clearance">
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Clearance Type</th> <!-- Added ClearanceType -->
                        <th>Student Count</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_clearances as $clearance): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($clearance['school_year']); ?></td>
                            <td><?php echo htmlspecialchars($clearance['semester']); ?></td>
                            <td><?php echo htmlspecialchars($clearance['startdate']); ?></td>
                            <td><?php echo htmlspecialchars($clearance['enddate']); ?></td>
                            <td><?php echo htmlspecialchars($clearance['clearancetype']); ?></td> <!-- Display ClearanceType -->
                            <td><?php echo htmlspecialchars($clearance['student_count']); ?></td>
                            <td>
                                <form action="ClearanceStudents.php" method="GET">
                                    <input type="hidden" name="cpid" value="<?php echo htmlspecialchars($clearance['Cpid']); ?>">
                                    <button type="submit">View</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
