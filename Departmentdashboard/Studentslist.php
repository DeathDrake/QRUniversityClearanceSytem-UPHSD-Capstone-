<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    header('Location: ../index.php');
    exit();
}

// Get the Cpid from the query parameter
$cpid = $_GET['cpid'] ?? ($_POST['cpid'] ?? null);
if (!$cpid) {
    die("Error: No Cpid specified.");
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'clearancedb');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the filter query
$filterQuery = "
    SELECT student_id, username, StudNo, fname, lname, course, year_level 
    FROM students
";

$conditions = [];
if (!empty($_GET['year_level'])) {
    $conditions[] = "year_level LIKE '%" . $conn->real_escape_string($_GET['year_level']) . "%'";
}
if (!empty($_GET['course'])) {
    $conditions[] = "course LIKE '%" . $conn->real_escape_string($_GET['course']) . "%'";
}
if (!empty($_GET['student_code'])) {
    $conditions[] = "StudNo LIKE '%" . $conn->real_escape_string($_GET['student_code']) . "%'";
}
if (!empty($_GET['fname'])) {
    $conditions[] = "fname LIKE '%" . $conn->real_escape_string($_GET['fname']) . "%'";
}
if (!empty($_GET['lname'])) {
    $conditions[] = "lname LIKE '%" . $conn->real_escape_string($_GET['lname']) . "%'";
}

if (!empty($conditions)) {
    $filterQuery .= " WHERE " . implode(" AND ", $conditions);
}

// Execute the query
$students = $conn->query($filterQuery);
if (!$students) {
    die("Query failed: " . $conn->error);
}

// Handle adding multiple students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_all'])) {
    if (!empty($_POST['selected_students'])) {
        foreach ($_POST['selected_students'] as $student_id) {
            // Check if the student is already added to the clearance period
            $check_stmt = $conn->prepare('SELECT COUNT(*) FROM clearance WHERE student_id = ? AND Cpid = ?');
            $check_stmt->bind_param('ii', $student_id, $cpid);
            $check_stmt->execute();
            $check_stmt->bind_result($exists);
            $check_stmt->fetch();
            $check_stmt->close();

            if (!$exists) {
                // Add the student to the clearance table
                $stmt = $conn->prepare('INSERT INTO clearance (student_id, Cpid, status) VALUES (?, ?, "Pending")');
                $stmt->bind_param('ii', $student_id, $cpid);
                $stmt->execute();
                $clearance_id = $conn->insert_id;
                $stmt->close();

                // Fetch all signatory IDs
                $signatories = $conn->query('SELECT signatory_id FROM signatory')->fetch_all(MYSQLI_ASSOC);

                // Insert clearance details for each signatory
                $clearance_detail_stmt = $conn->prepare('
                    INSERT INTO clearance_details (clearance_id, student_id, signatory_id, deptstatus, Cpid)
                    VALUES (?, ?, ?, "Not Set", ?)
                ');
                foreach ($signatories as $signatory) {
                    $clearance_detail_stmt->bind_param('iiii', $clearance_id, $student_id, $signatory['signatory_id'], $cpid);
                    $clearance_detail_stmt->execute();
                }
                $clearance_detail_stmt->close();
            }
        }
        header("Location: Studentslist.php?cpid=$cpid");
        exit();
    }
}

// Handle adding individual students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Check if the student is already added to the clearance period
    $check_stmt = $conn->prepare('SELECT COUNT(*) FROM clearance WHERE student_id = ? AND Cpid = ?');
    $check_stmt->bind_param('ii', $student_id, $cpid);
    $check_stmt->execute();
    $check_stmt->bind_result($exists);
    $check_stmt->fetch();
    $check_stmt->close();

    if (!$exists) {
        // Add the student to the clearance table
        $stmt = $conn->prepare('INSERT INTO clearance (student_id, Cpid, status) VALUES (?, ?, "Pending")');
        $stmt->bind_param('ii', $student_id, $cpid);
        $stmt->execute();
        $clearance_id = $conn->insert_id;
        $stmt->close();

        // Fetch all signatory IDs
        $signatories = $conn->query('SELECT signatory_id FROM signatory')->fetch_all(MYSQLI_ASSOC);

        // Insert clearance details for each signatory
        $clearance_detail_stmt = $conn->prepare('
            INSERT INTO clearance_details (clearance_id, student_id, signatory_id, deptstatus, Cpid)
            VALUES (?, ?, ?, "Not Set", ?)
        ');
        foreach ($signatories as $signatory) {
            $clearance_detail_stmt->bind_param('iiii', $clearance_id, $student_id, $signatory['signatory_id'], $cpid);
            $clearance_detail_stmt->execute();
        }
        $clearance_detail_stmt->close();

        header("Location: Studentslist.php?cpid=$cpid");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Students List</title>
    <link rel="stylesheet" href="Studentslist.css">
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


        .students-table input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 5px;
        }

        /* Fixed Back Button Styling */
        .back-button {
            position: fixed; /* Keeps the button in place */
            bottom: 20px; /* Distance from the bottom of the viewport */
            right: 20px; /* Distance from the right of the viewport */
            background-color: #b22222;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000; /* Ensures it stays above other elements */
        }

        .back-button:hover {
            background-color: #8b0000; /* Darker red on hover */
        }
    </style>
    <script>
        function toggleCheckAll(source) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
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
                <h1>Students List for Clearance Period (Cpid: <?php echo htmlspecialchars($cpid); ?>)</h1>
            </div>
        </header>

        <!-- Filters -->
        <form method="GET" action="Studentslist.php" class="filters">
            <input type="hidden" name="cpid" value="<?= htmlspecialchars($cpid) ?>">
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

        <!-- Actions -->
        <form method="POST" action="Studentslist.php">
            <input type="hidden" name="cpid" value="<?= htmlspecialchars($cpid) ?>">
            <div class="delete-all-container">
                <button type="submit" name="add_all" value="1">Add All Selected</button>
            </div>

            <!-- Students Table -->
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
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_students[]" value="<?php echo htmlspecialchars($student['student_id']); ?>" class="student-checkbox"></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['StudNo']); ?></td>
                                <td><?php echo htmlspecialchars($student['fname']); ?></td>
                                <td><?php echo htmlspecialchars($student['lname']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                        <input type="hidden" name="cpid" value="<?= htmlspecialchars($cpid) ?>">
                                        <button type="submit" class="btn-add">Add</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Fixed Back Button -->
        <button class="back-button" onclick="window.history.back();">Back</button>
        <a href="Signatory_clearanceforms.php" class="back-button">Back</a>
    </div>
</body>
</html>
