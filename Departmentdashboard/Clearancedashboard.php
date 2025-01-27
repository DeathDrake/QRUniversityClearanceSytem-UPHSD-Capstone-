<?php
// Include the database connection
require_once '../Database/clearancedb.php';
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is a Signatory Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    header('Location: ../index.php');
    exit();
}

// Check if signatory_id is set in the session
if (!isset($_SESSION['signatory_id'])) {
    die("Error: Signatory ID is not set in the session.");
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'clearancedb');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all clearance periods for the dropdown
$clearance_periods = $conn->query("SELECT * FROM clearance_period");
if (!$clearance_periods) {
    die("Query failed: " . $conn->error);
}

// Get selected Cpid (clearance period ID) from the dropdown
$selected_cpid = $_GET['cpid'] ?? null;

// Handle signing selected students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sign_selected') {
    $student_ids = json_decode($_POST['student_ids'], true);
    $signatory_id = $_SESSION['signatory_id'];
    $cpid = $_POST['cpid']; // Get the selected clearance period ID

    if (is_array($student_ids) && !empty($student_ids) && !empty($cpid)) {
        $stmt = $conn->prepare("UPDATE clearance_details SET deptstatus = 'Cleared', lackingreq = NULL WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
        foreach ($student_ids as $student_id) {
            $stmt->bind_param("iii", $student_id, $signatory_id, $cpid);
            $stmt->execute();
        }
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid student IDs or clearance period']);
    }
    exit();
}

// Handle automatic update for Not Set to Cleared
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_to_cleared') {
    $student_id = $_POST['student_id'];
    $signatory_id = $_SESSION['signatory_id'];
    $cpid = $_POST['cpid'];

    $stmt = $conn->prepare("UPDATE clearance_details SET deptstatus = 'Cleared', lackingreq = NULL WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
    $stmt->bind_param("iii", $student_id, $signatory_id, $cpid);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update to Cleared.']);
    }
    $stmt->close();
    exit();
}

// Handle form submission to save, update, or clear a note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $student_id = $_POST['student_id'];
    $signatory_id = $_SESSION['signatory_id'];
    $cpid = $_POST['cpid'];

    if (isset($_POST['save_note'])) {
        $note = $_POST['lackingrequirement'] ?? '';
        $status = !empty($note) ? 'Pending' : 'Not Set';

        $clearance_stmt = $conn->prepare("SELECT clearance_id FROM clearance WHERE student_id = ? AND Cpid = ?");
        $clearance_stmt->bind_param("ii", $student_id, $cpid);
        $clearance_stmt->execute();
        $clearance_result = $clearance_stmt->get_result();
        $clearance_id = $clearance_result->fetch_assoc()['clearance_id'];
        $clearance_stmt->close();

        $stmt = $conn->prepare("SELECT clearance_detail_id FROM clearance_details WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
        $stmt->bind_param("iii", $student_id, $signatory_id, $cpid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE clearance_details SET lackingreq = ?, clearance_id = ?, deptstatus = ? WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
            $stmt->bind_param("sisiii", $note, $clearance_id, $status, $student_id, $signatory_id, $cpid);
        } else {
            $stmt = $conn->prepare("INSERT INTO clearance_details (student_id, signatory_id, lackingreq, clearance_id, deptstatus, Cpid) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisisi", $student_id, $signatory_id, $note, $clearance_id, $status, $cpid);
        }

        if ($stmt->execute()) {
            header("Location: Clearancedashboard.php");
            exit();
        } else {
            echo "Error saving note: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['delete_note'])) {
        $stmt = $conn->prepare("UPDATE clearance_details SET lackingreq = NULL, deptstatus = 'Not Set' WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
        $stmt->bind_param("iii", $student_id, $signatory_id, $cpid);
        if ($stmt->execute()) {
            header("Location: Clearancedashboard.php");
            exit();
        } else {
            echo "Error clearing note: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve student records with optional filters
$filterQuery = "
    SELECT DISTINCT students.student_id, students.StudNo, students.username, students.fname, students.mname, students.lname, 
           students.course, students.year_level, clearance.Cpid
    FROM students
    LEFT JOIN clearance ON students.student_id = clearance.student_id
    LEFT JOIN clearance_details ON clearance.student_id = clearance_details.student_id";

$conditions = [];
if (!empty($_GET['year_level'])) {
    $conditions[] = "students.year_level LIKE '%" . $conn->real_escape_string($_GET['year_level']) . "%'";
}
if (!empty($_GET['state'])) {
    $conditions[] = "state LIKE '%" . $conn->real_escape_string($_GET['state']) . "%'";
}
if (!empty($_GET['course'])) {
    $conditions[] = "students.course LIKE '%" . $conn->real_escape_string($_GET['course']) . "%'";
}
if (!empty($_GET['student_code'])) {
    $conditions[] = "students.StudNo LIKE '%" . $conn->real_escape_string($_GET['student_code']) . "%'";
}
if ($selected_cpid) {
    $conditions[] = "clearance.Cpid = " . intval($selected_cpid);
} else {
    $conditions[] = "clearance_details.student_id IS NOT NULL";
}

if (count($conditions) > 0) {
    $filterQuery .= " WHERE " . implode(" AND ", $conditions);
}
$students = $conn->query($filterQuery);
if (!$students) {
    die("Query failed: " . $conn->error);
}

// Function to retrieve notes and status for each student
function getNotes($conn, $student_id, $signatory_id, $cpid) {
    if (empty($signatory_id) || empty($cpid)) {
        return ['lackingreq' => '', 'deptstatus' => 'Not Set'];
    }

    $stmt = $conn->prepare("SELECT lackingreq, deptstatus FROM clearance_details WHERE student_id = ? AND signatory_id = ? AND Cpid = ?");
    $stmt->bind_param("iii", $student_id, $signatory_id, $cpid);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: ['lackingreq' => '', 'deptstatus' => 'Not Set'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Dashboard</title>
    <link rel="stylesheet" href="Clearancedashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
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
</style>
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
                <h1>Online Clearance System</h1>
            </div>
        </header>

        <div class="controls">
            <div class="filters">
                <form method="GET" action="Clearancedashboard.php">
                   
               
                    <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($_GET['course'] ?? ''); ?>">
                    <input type="text" name="student_code" placeholder="Student Code" value="<?php echo htmlspecialchars($_GET['student_code'] ?? ''); ?>">
                    <select name="year_level" id="year_level" onchange="filterByYearLevel()">
            <option value="">All Year Level</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
        </select>

                      <!-- Add Status Filter Dropdown -->
        <select name="deptstatus" id="deptstatus" onchange="filterByStatus()">
            <option value="">All Status</option>
            <option value="Cleared">Cleared</option>
            <option value="Pending">Pending</option>
            <option value="Not Set">Not Set</option>
        </select>

        <select name="cpid" onchange="this.form.submit()">
                        <option value="">All Clearance Period</option>
                        <?php while ($period = $clearance_periods->fetch_assoc()): ?>
                            <option value="<?php echo $period['Cpid']; ?>" <?php echo ($period['Cpid'] == $selected_cpid) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($period['school_year'] . " | " . $period['semester'] . " | " . $period['startdate'] . " - " . $period['enddate']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button class="btn-search" type="submit">Search</button>
                </form>
            </div>
            <button class="btn-green" onclick="clearSelected()">Sign Selected</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>
                        <label class="checkbox">
                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                            <span class="checkmark"></span>
                            <span class="label">Select All</span>
                        </label>
                    </th>
                    <th>View</th>
                    <th>Username</th>
                    <th>Student No</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>State</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody id="student-list">
                <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td>
                        <label class="checkbox">
                            <input type="checkbox" name="select_student[]" value="<?php echo $student['student_id']; ?>">
                            <span class="checkmark"></span>
                        </label>
                    </td>
                    <td>
                        <form action="studentform.php" method="GET">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                            <input type="hidden" name="cpid" value="<?php echo htmlspecialchars($student['Cpid']); ?>">
                            <button type="submit" class="btn-view">View</button>
                        </form>
                    </td>
                    <td><?php echo htmlspecialchars($student['username'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['StudNo'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['fname'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['mname'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['lname'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['course'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['year_level'] ?? ''); ?></td>
                    <td>
                        <?php
                        $notes = getNotes($conn, $student['student_id'], $_SESSION['signatory_id'], $student['Cpid']);
                        $noteText = htmlspecialchars($notes['lackingreq'] ?? '');
                        $stateText = htmlspecialchars($notes['deptstatus'] ?? 'Not Set');
                        $buttonClass = ($stateText === 'Pending') ? 'btn-pending' : (($stateText === 'Cleared') ? 'btn-cleared' : 'btn-notset');
                        ?>
                        <button type="button" class="btn-state <?php echo $buttonClass; ?>" onclick="toggleAndSaveStatus(this, '<?php echo $student['student_id']; ?>', '<?php echo $student['Cpid']; ?>')">
                            <?php echo $stateText; ?>
                        </button>
                    </td>
                    <td>
                        <form method="POST" class="note-form">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                            <input type="hidden" name="deptstatus" class="status-input" value="<?php echo $stateText; ?>">
                            <input type="hidden" name="cpid" value="<?php echo htmlspecialchars($student['Cpid']); ?>">
                            <textarea name="lackingrequirement" placeholder="Add note here..."><?php echo $noteText; ?></textarea>
                            <button type="submit" name="save_note">Save</button>
                            <button type="submit" name="delete_note" onclick="return confirm('Are you sure?');">Clear Note</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="student-form-container"></div>

<script>
    
    function filterByYearLevel() {
        const yearFilter = document.getElementById('year_level').value; // Get the selected year level
        const tableRows = document.querySelectorAll('#student-list tr'); // Select all table rows

        tableRows.forEach(row => {
            const yearCell = row.querySelector('td:nth-child(9)'); // Adjust this index to match the "Year Level" column
            const yearText = yearCell ? yearCell.textContent.trim() : '';

            // Show or hide rows based on the selected year level
            if (!yearFilter || yearText === yearFilter) {
                row.style.display = ''; // Show row
            } else {
                row.style.display = 'none'; // Hide row
            }
        });
    }

    function filterByStatus() {
        const statusFilter = document.getElementById('deptstatus').value; // Get the selected status
        const tableRows = document.querySelectorAll('#student-list tr'); // Select all rows in the table

        tableRows.forEach(row => {
            const statusButton = row.querySelector('.btn-state'); // Find the status element in the row
            const statusText = statusButton ? statusButton.textContent.trim() : '';

            // Show or hide rows based on the selected status
            if (!statusFilter || statusText === statusFilter) {
                row.style.display = ''; // Show row
            } else {
                row.style.display = 'none'; // Hide row
            }
        });
    }


    function toggleAndSaveStatus(button, studentId, cpid) {
        let statusInput = button.closest('td').nextElementSibling.querySelector('.status-input');

        if (button.textContent.trim() === 'Not Set') {
            button.textContent = 'Cleared';
            button.classList.remove('btn-notset');
            button.classList.add('btn-cleared');
            statusInput.value = 'Cleared';

            fetch('Clearancedashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'update_to_cleared',
                    student_id: studentId,
                    cpid: cpid
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        } else if (button.textContent.trim() === 'Cleared') {
            button.textContent = 'Not Set';
            button.classList.remove('btn-cleared');
            button.classList.add('btn-notset');
            statusInput.value = 'Not Set';
        }
    }

    function applyFilters() {
        let params = new URLSearchParams();
        ['year_level', 'state', 'course', 'student_code', 'cpid'].forEach(id => {
            let input = document.querySelector(`input[name="${id}"], select[name="${id}"]`);
            if (input && input.value) {
                params.set(id, input.value);
            }
        });
        window.location.search = params.toString();
    }

    function toggleSelectAll(selectAllCheckbox) {
        const studentCheckboxes = document.querySelectorAll('input[name="select_student[]"]');
        studentCheckboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
    }

    function clearSelected() {
        const selectedStudents = document.querySelectorAll('input[name="select_student[]"]:checked');
        const studentIds = Array.from(selectedStudents).map(input => input.value);

        const cpid = document.querySelector('select[name="cpid"]').value;

        if (studentIds.length === 0) {
            alert("No students selected.");
            return;
        }

        if (!cpid) {
            alert("Please select a clearance period.");
            return;
        }

        fetch('Clearancedashboard.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'sign_selected',
                student_ids: JSON.stringify(studentIds),
                cpid: cpid
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Selected students have been signed.");
                    selectedStudents.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        const statusButton = row.querySelector('.btn-state');
                        statusButton.textContent = 'Cleared';
                        statusButton.classList.remove('btn-pending', 'btn-notset');
                        statusButton.classList.add('btn-cleared');
                    });
                } else {
                    alert("Failed to sign students. " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred.");
            });
    }
</script>

</body>
</html>