<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get the Cpid from the query parameter
$cpid = $_GET['cpid'] ?? null;
if (!$cpid) {
    die("Error: No Clearance Period ID provided.");
}

// Prepare the query for fetching students with their clearance status
$query = "
    SELECT s.username, s.StudNo, s.fname, s.lname, s.course, s.year_level, c.status
    FROM clearance c
    JOIN students s ON c.student_id = s.student_id
    WHERE c.Cpid = :cpid
";

$conditions = ['cpid' => $cpid];

if (!empty($_GET['year_level'])) {
    $query .= " AND s.year_level LIKE :year_level";
    $conditions['year_level'] = "%" . $_GET['year_level'] . "%";
}
if (!empty($_GET['course'])) {
    $query .= " AND s.course LIKE :course";
    $conditions['course'] = "%" . $_GET['course'] . "%";
}
if (!empty($_GET['student_code'])) {
    $query .= " AND s.StudNo LIKE :student_code";
    $conditions['student_code'] = "%" . $_GET['student_code'] . "%";
}
if (!empty($_GET['fname'])) {
    $query .= " AND s.fname LIKE :fname";
    $conditions['fname'] = "%" . $_GET['fname'] . "%";
}
if (!empty($_GET['lname'])) {
    $query .= " AND s.lname LIKE :lname";
    $conditions['lname'] = "%" . $_GET['lname'] . "%";
}

// Execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($conditions);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Students (Admin)</title>
    <link rel="stylesheet" href="ClearanceStudentsAdm.css">
</head>
<script>
    // Confirmation dialog for logout
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>
<body>
    <div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="Admin_dashboard.php" class="button">Dashboard</a></li>
            <li><a href="Admin_Create_Account.php" class="button">Create Account</a></li>
            <li><a href="Create_Clearanceform.php" class="button">Create Clearance Form</a></li>
        </ul>
        <div class="sidebar-bottom">
        <a href="logout.php" onclick="return confirmLogout();" class="button">
        
            <p>Logged in as: <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Admin'); ?></p>
        </div>
    </div>

    <div class="main-content">
        <header>
            <h1>Clearance Students</h1>
        </header>

        <form method="GET" action="ClearanceStudentsAdm.php" class="filters">
            <input type="hidden" name="cpid" value="<?php echo htmlspecialchars($cpid); ?>">
            <input type="text" name="year_level" placeholder="Year Level" value="<?php echo htmlspecialchars($_GET['year_level'] ?? ''); ?>">
            <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($_GET['course'] ?? ''); ?>">
            <input type="text" name="student_code" placeholder="Student Code" value="<?php echo htmlspecialchars($_GET['student_code'] ?? ''); ?>">
            <input type="text" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($_GET['fname'] ?? ''); ?>">
            <input type="text" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($_GET['lname'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="students-table">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Student No</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['status']); ?></td>
                                <td><?php echo htmlspecialchars($student['StudNo']); ?></td>
                                <td><?php echo htmlspecialchars($student['fname']); ?></td>
                                <td><?php echo htmlspecialchars($student['lname']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No students found for this clearance period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
