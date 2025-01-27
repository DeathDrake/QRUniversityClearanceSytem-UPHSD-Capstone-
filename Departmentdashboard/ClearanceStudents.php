<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    header('Location: ../index.php');
    exit();
}

// Get the Cpid from the query parameter
$cpid = $_GET['cpid'] ?? null;
if (!$cpid) {
    die("Error: No Cpid specified.");
}

// Handle single student delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    $clearance_id = $_POST['clearance_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete from clearance_details
        $stmt = $pdo->prepare("DELETE FROM clearance_details WHERE clearance_id = ? AND student_id = ? AND Cpid = ?");
        $stmt->execute([$clearance_id, $student_id, $cpid]);

        // Delete from clearance
        $stmt = $pdo->prepare("DELETE FROM clearance WHERE clearance_id = ? AND student_id = ? AND Cpid = ?");
        $stmt->execute([$clearance_id, $student_id, $cpid]);

        // Commit transaction
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error deleting student: " . $e->getMessage());
    }

    // Redirect back to the page
    header("Location: ClearanceStudents.php?cpid=" . urlencode($cpid));
    exit();
}

// Handle multiple students delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $selected_students = $_POST['selected_students'] ?? [];

    if (!empty($selected_students)) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            foreach ($selected_students as $clearance_id) {
                // Delete from clearance_details
                $stmt = $pdo->prepare("DELETE FROM clearance_details WHERE clearance_id = ? AND Cpid = ?");
                $stmt->execute([$clearance_id, $cpid]);

                // Delete from clearance
                $stmt = $pdo->prepare("DELETE FROM clearance WHERE clearance_id = ? AND Cpid = ?");
                $stmt->execute([$clearance_id, $cpid]);
            }

            // Commit transaction
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error deleting selected students: " . $e->getMessage());
        }

        // Redirect back to the page
        header("Location: ClearanceStudents.php?cpid=" . urlencode($cpid));
        exit();
    }
}

// Prepare the filter query
$filterQuery = "
    SELECT c.clearance_id, s.student_id, s.username, s.StudNo, s.fname, s.lname, s.course, s.year_level 
    FROM clearance c 
    JOIN students s ON c.student_id = s.student_id 
    WHERE c.Cpid = ?
";

$conditions = [];
$parameters = [$cpid];

if (!empty($_GET['year_level'])) {
    $conditions[] = "s.year_level LIKE ?";
    $parameters[] = "%" . $_GET['year_level'] . "%";
}
if (!empty($_GET['course'])) {
    $conditions[] = "s.course LIKE ?";
    $parameters[] = "%" . $_GET['course'] . "%";
}
if (!empty($_GET['student_code'])) {
    $conditions[] = "s.StudNo LIKE ?";
    $parameters[] = "%" . $_GET['student_code'] . "%";
}
if (!empty($_GET['fname'])) {
    $conditions[] = "s.fname LIKE ?";
    $parameters[] = "%" . $_GET['fname'] . "%";
}
if (!empty($_GET['lname'])) {
    $conditions[] = "s.lname LIKE ?";
    $parameters[] = "%" . $_GET['lname'] . "%";
}

if (!empty($conditions)) {
    $filterQuery .= " AND " . implode(" AND ", $conditions);
}

// Execute the query
$stmt = $pdo->prepare($filterQuery);
$stmt->execute($parameters);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ClearanceStudents.css">
    <style>
        /* Button Styling */
        button {
            background-color: #b22222;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #8b0000;
        }

        .delete-all-container {
            margin-bottom: 10px;
        }

        /* Filters Styling */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filters select, .filters input[type="text"], .filters button {
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 180px; /* Adjust width to match */
        }

        .filters button {
            width: 120px; /* Slightly smaller width for the Search button */
            background-color: #f00;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filters button:hover {
            background-color: #d00;
        }

        /* Back Button Styling */
        .back-button {
            position: fixed; /* Keeps the button fixed at the same position */
            bottom: 20px; /* Distance from the bottom of the viewport */
            right: 20px; /* Distance from the right of the viewport */
            background-color: #b22222; /* Red background */
            color: white; /* White text color */
            padding: 10px 20px; /* Button padding */
            font-size: 16px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            text-align: center; /* Center the text */
            border: none; /* Remove border */
            cursor: pointer; /* Change cursor to pointer */
            z-index: 1000; /* Ensure it stays on top of other elements */
        }

        .back-button:hover {
            background-color: #8b0000; /* Darker red color on hover */
        }
    </style>
    <script>
        // Toggle "Check All" functionality
        function toggleCheckAll(source) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        // Confirm deletion of selected students
        function confirmDeleteAll() {
            return confirm('Are you sure you want to delete all selected students?');
        }
    </script>
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
        <header>
            <div class="header-container">
                <h1>Clearance Students for Clearance Period (Cpid: <?php echo htmlspecialchars($cpid); ?>)</h1>
            </div>
        </header>

        <!-- Filters -->
        <form method="GET" action="ClearanceStudents.php" class="filters">
            <input type="hidden" name="cpid" value="<?= htmlspecialchars($cpid) ?>">

            <!-- Year Level Dropdown -->
            <select name="year_level">
                <option value="">All Year Level</option>
                <option value="1st Year" <?php echo isset($_GET['year_level']) && $_GET['year_level'] === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                <option value="2nd Year" <?php echo isset($_GET['year_level']) && $_GET['year_level'] === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3rd Year" <?php echo isset($_GET['year_level']) && $_GET['year_level'] === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4th Year" <?php echo isset($_GET['year_level']) && $_GET['year_level'] === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
            </select>

            <!-- Course Dropdown -->
            <select name="course">
    <option value="">All Course</option>
    <option value="BACHELOR OF ELEMENTARY EDUCATION" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF ELEMENTARY EDUCATION' ? 'selected' : ''; ?>>BACHELOR OF ELEMENTARY EDUCATION</option>
    <option value="BACHELOR OF ARTS" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF ARTS' ? 'selected' : ''; ?>>BACHELOR OF ARTS</option>
    <option value="BACHELOR OF ARTS IN COMMUNICATION" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF ARTS IN COMMUNICATION' ? 'selected' : ''; ?>>BACHELOR OF ARTS IN COMMUNICATION</option>
    <option value="BACHELOR OF ARTS IN PSYCHOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF ARTS IN PSYCHOLOGY' ? 'selected' : ''; ?>>BACHELOR OF ARTS IN PSYCHOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN ACCOUNTANCY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ACCOUNTANCY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ACCOUNTANCY</option>
    <option value="BACHELOR OF SCIENCE IN ACCOUNTING TECHNOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ACCOUNTING TECHNOLOGY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ACCOUNTING TECHNOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN ARCHITECTURE" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ARCHITECTURE' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ARCHITECTURE</option>
    <option value="BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION</option>
    <option value="BACHELOR OF SCIENCE IN CIVIL ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN CIVIL ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN CIVIL ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN CIVIL ENGINEERING (STRUCTURAL ENGINEERING)" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN CIVIL ENGINEERING (STRUCTURAL ENGINEERING)' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN CIVIL ENGINEERING (STRUCTURAL ENGINEERING)</option>
    <option value="BACHELOR OF SCIENCE IN COMMERCE" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN COMMERCE' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN COMMERCE</option>
    <option value="BACHELOR OF SCIENCE IN COMPUTER ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN COMPUTER ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN COMPUTER ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN COMPUTER SCIENCE" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN COMPUTER SCIENCE' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN COMPUTER SCIENCE</option>
    <option value="BACHELOR OF SCIENCE IN COMPUTER SCIENCE WITH BPO" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN COMPUTER SCIENCE WITH BPO' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN COMPUTER SCIENCE WITH BPO</option>
    <option value="BACHELOR OF SCIENCE IN CRIMINOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN CRIMINOLOGY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN CRIMINOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN ELECTRONICS AND COMMUNICATIONS ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ELECTRONICS AND COMMUNICATIONS ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ELECTRONICS AND COMMUNICATIONS ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN ENTREPRENEURSHIP" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN ENTREPRENEURSHIP</option>
    <option value="BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT</option>
    <option value="BACHELOR OF SCIENCE IN HOTEL AND RESTAURANT MANAGEMENT" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN HOTEL AND RESTAURANT MANAGEMENT' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN HOTEL AND RESTAURANT MANAGEMENT</option>
    <option value="BACHELOR OF SCIENCE IN INDUSTRIAL ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN INDUSTRIAL ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN INDUSTRIAL ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH BPO" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH BPO' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH BPO</option>
    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH SPECIALIZATION IN GAME DEVELOPMENT" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH SPECIALIZATION IN GAME DEVELOPMENT' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH SPECIALIZATION IN GAME DEVELOPMENT</option>
    <option value="BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING</option>
    <option value="BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN NURSING" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN NURSING' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN NURSING</option>
    <option value="BACHELOR OF SCIENCE IN OCCUPATIONAL THERAPY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN OCCUPATIONAL THERAPY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN OCCUPATIONAL THERAPY</option>
    <option value="BACHELOR OF SCIENCE IN PHARMACY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN PHARMACY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN PHARMACY</option>
    <option value="BACHELOR OF SCIENCE IN PHYSICAL THERAPY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN PHYSICAL THERAPY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN PHYSICAL THERAPY</option>
    <option value="BACHELOR OF SCIENCE IN RADIOLOGIC TECHNOLOGY" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN RADIOLOGIC TECHNOLOGY' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN RADIOLOGIC TECHNOLOGY</option>
    <option value="BACHELOR OF SCIENCE IN TOURISM" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN TOURISM' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN TOURISM</option>
    <option value="BACHELOR OF SCIENCE IN TOURISM MANAGEMENT" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SCIENCE IN TOURISM MANAGEMENT' ? 'selected' : ''; ?>>BACHELOR OF SCIENCE IN TOURISM MANAGEMENT</option>
    <option value="BACHELOR OF SECONDARY EDUCATION" <?php echo isset($_GET['course']) && $_GET['course'] === 'BACHELOR OF SECONDARY EDUCATION' ? 'selected' : ''; ?>>BACHELOR OF SECONDARY EDUCATION</option>
</select>


            <input type="text" name="student_code" placeholder="Student Code" value="<?php echo htmlspecialchars($_GET['student_code'] ?? ''); ?>">
            <input type="text" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($_GET['fname'] ?? ''); ?>">
            <input type="text" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($_GET['lname'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Students Table -->
        <form method="POST" onsubmit="return confirmDeleteAll();">
            <div class="delete-all-container">
                <button type="submit" name="delete_selected">Delete All Selected</button>
            </div>

            <div class="students-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="toggleCheckAll(this)"></th>
                            <th>Username</th>
                            <th>Student No</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_students[]" value="<?php echo htmlspecialchars($student['clearance_id']); ?>" class="student-checkbox"></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['StudNo']); ?></td>
                                <td><?php echo htmlspecialchars($student['fname']); ?></td>
                                <td><?php echo htmlspecialchars($student['lname']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                        <input type="hidden" name="clearance_id" value="<?php echo htmlspecialchars($student['clearance_id']); ?>">
                                        <button type="submit" name="delete_student" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Fixed Back Button -->
        <a href="Signatory_clearanceforms.php" class="back-button">Back</a>
    </div>
</body>
</html>
