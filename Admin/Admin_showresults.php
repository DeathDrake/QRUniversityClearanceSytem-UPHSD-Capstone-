<?php
require_once '../Database/clearancedb.php';
session_start();

// Validate if `cpid` and `userid` are provided
if (!isset($_GET['cpid']) || !isset($_GET['user_id'])) {
    die("Missing required parameters.");
}

$cpid = intval($_GET['cpid']);
$userid = intval($_GET['user_id']);

// Fetch student details based on `userid`
$student_stmt = $pdo->prepare('SELECT student_id, fname, mname, lname, StudNo, course FROM students WHERE user_id = ?');
$student_stmt->execute([$userid]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

// Fetch clearance details based on `cpid` and `student_id`
$clearance_stmt = $pdo->prepare('
    SELECT s.signatory_department, cd.deptstatus AS status, cd.lackingreq AS notes
    FROM signatory s
    LEFT JOIN clearance_details cd 
    ON s.signatory_id = cd.signatory_id 
    AND cd.clearance_id = (
        SELECT clearance_id FROM clearance 
        WHERE Cpid = ? AND student_id = ?
    )
    ORDER BY s.signatory_id
');
$clearance_stmt->execute([$cpid, $student['student_id']]);
$clearance_data = $clearance_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Clearance Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: maroon;
        }

        .student-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .student-info p {
            margin: 5px 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background: maroon;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: maroon;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #a01919;
        }
    </style>
</head>
<body>
    <h1>Clearance Details</h1>

    <div class="student-info">
        <p><strong>Student No:</strong> <?php echo htmlspecialchars($student['StudNo']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['fname'] . ' ' . $student['mname'] . ' ' . $student['lname']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($clearance_data) > 0): ?>
                <?php foreach ($clearance_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['signatory_department']); ?></td>
                        <td><?php echo htmlspecialchars($row['status'] ?? 'Not Set'); ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No clearance details available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="Admin_dashboard.php" class="back-button">Back</a>
</body>
</html>
