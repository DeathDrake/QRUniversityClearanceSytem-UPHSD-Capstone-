<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the logged-in user_id and role from session
$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Initialize email
$email = null;

// Determine the role and fetch the email
try {
    if ($role === 'admin') {
        $statement = $pdo->prepare('SELECT email FROM admin WHERE user_id = ?');
        $statement->execute([$user_id]);
        $email = $statement->fetchColumn();
    } 
    

    // Debugging log if no email found
    if (!$email) {
        error_log("Email not found for user_id: $user_id with role: $role");
        $email = "Email not available";
    }
} catch (PDOException $e) {
    // Log database errors
    error_log("Database error: " . $e->getMessage());
    $email = "Error fetching email";
}

// Fetch existing CPIDs and their status counts along with semester names
$cpidData = [];
try {
    $cpidQuery = $pdo->prepare("
        SELECT 
            cp.cpid, 
            cp.semester, 
            SUM(CASE WHEN c.status = 'Complete' THEN 1 ELSE 0 END) AS complete_count,
            SUM(CASE WHEN c.status = 'Pending' THEN 1 ELSE 0 END) AS pending_count
        FROM clearance_period cp
        LEFT JOIN clearance c ON cp.cpid = c.cpid
        GROUP BY cp.cpid, cp.semester
    ");
    $cpidQuery->execute();
    $results = $cpidQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $cpidData[$row['cpid']] = [
            'semester' => $row['semester'],
            'Complete' => $row['complete_count'],
            'Pending' => $row['pending_count']
        ];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Set session data
$_SESSION['user_id'] = $user_id;
$_SESSION['role'] = $role;

// Get the selected directory type from the form (default to 'user')
$directoryType = isset($_POST['directoryType']) ? $_POST['directoryType'] : 'user';

// Set the SQL query based on the selected directory type
if ($directoryType == 'admin') {
    $sql = "SELECT admin_id, user_id, first_name, last_name, email, type, signatory_id FROM admin";
} elseif ($directoryType == 'student') {
    $sql = "SELECT student_id, user_id, StudNo, fname, mname, lname, course, year_level, email FROM students";
} else {
    $sql = "SELECT user_id, username, role FROM user";
}

$statement = $pdo->prepare($sql);
$statement->execute();
$result = $statement->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Admin_dashboard.css">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
       
        .sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            width: 250px;
            background-color: #800000;
        }

        .logo {
            text-align: center;
            padding: 10px;
            color: #fff;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
        }

        a.button, p {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            text-align: center;
        }

        a.button:hover {
            background-color: #34495e;
        }

        /* Stick "Log Out" and "Logged in as" to the bottom */
        .sidebar-bottom {
            margin-top: auto;
            text-align: center;
        }

        .sidebar-bottom a.button {
            margin-bottom: 0;
        }

        .sidebar-bottom p {
            margin-top: 0;
        }
        
    </style>
</head>
<body>
<div class="sidebar">
    <!-- Add the logo -->
    <img src="images/perpetualsmallicon.png" alt="Perpetual Logo" class="logo-image">

    <ul>
        <li>
            <a href="Admin_dashboard.php" class="button">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="Admin_Create_Account.php" class="button">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </li>
        <li>
            <a href="Create_Clearanceform.php" class="button">
                <i class="fas fa-file-alt"></i> Create ClearanceForm
            </a>
        </li>
        <li>
            <a href="#" class="button" onclick="openQRModal()">
                <i class="fas fa-qrcode"></i> Scan QR
            </a>
        </li>
        <li>
            <a href="Graph.php" class="button">
                <i class="fas fa-chart-bar"></i> Graph
            </a>
        </li>
        
    </ul>
    <!-- Sidebar bottom: Log Out and Logged in as -->

    <div class="sidebar-bottom">
    <a href="../index.php" onclick="return confirmLogout();" class="button">
    <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
        <p>Logged in as: <?= htmlspecialchars($email); ?></p>

        </div>
    </div>
    </div>


<div class="main-content">
<div class="card" style="font-weight: bold;">
    Admin Dashboard
</div>

    <!-- Dropdown for selecting the directory type -->
    <form method="POST" action="Admin_dashboard.php">
        <label for="directoryType">Choose a directory:</label>
        <select name="directoryType" id="directoryType" onchange="this.form.submit()">
            <option value="user" <?php if ($directoryType == 'user') echo 'selected'; ?>>User Directory</option>
            <option value="admin" <?php if ($directoryType == 'admin') echo 'selected'; ?>>Admin Directory</option>
            <option value="student" <?php if ($directoryType == 'student') echo 'selected'; ?>>Student Directory</option>
        </select>
    </form>

    <!-- Modal for Scan QR -->
    <div id="qrScanModal" class="modal" style="display:none">
        <div class="modal-content">
            <span class="close" onclick="closeQRModal()">&times;</span>
            <h3>Scan QR</h3>
            <p><div id="reader"></div></p>
        </div>
    </div>

    <!-- Directory Section -->
    <div class="directory-section">
        <h2>
            <?php
            if ($directoryType == 'admin') {
                echo "Admin Directory";
            } elseif ($directoryType == 'student') {
                echo "Student Directory";
            } else {
                echo "User Directory";
            }
            ?>
        </h2>

        <div class="directory-section">
    <h2>Clearance Status Overview</h2>
    <div id="chartsContainer" style="display: flex; flex-wrap: wrap; gap: 20px;"></div>
</div>



        <!-- User Table -->
        <table>
            <thead>
                <tr>
                    <?php
                    if ($directoryType == 'admin') {
                        echo "<th>Admin ID</th><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Type</th><th>Signatory ID</th>";
                    } elseif ($directoryType == 'student') {
                        echo "<th>Student ID</th><th>User ID</th><th>Student Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Course</th><th>Year Level</th><th>Email</th>";
                    } else {
                        echo "<th>User ID</th><th>Username</th><th>Role</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        echo "<tr>";
                        if ($directoryType == 'admin') {
                            echo "<td>" . $row['admin_id'] . "</td>";
                            echo "<td>" . $row['user_id'] . "</td>";
                            echo "<td>" . $row['first_name'] . "</td>";
                            echo "<td>" . $row['last_name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['type'] . "</td>";
                            echo "<td>" . $row['signatory_id'] . "</td>";
                        } elseif ($directoryType == 'student') {
                            echo "<td>" . $row['student_id'] . "</td>";
                            echo "<td>" . $row['user_id'] . "</td>";
                            echo "<td>" . $row['StudNo'] . "</td>";
                            echo "<td>" . $row['fname'] . "</td>";
                            echo "<td>" . $row['mname'] . "</td>";
                            echo "<td>" . $row['lname'] . "</td>";
                            echo "<td>" . $row['course'] . "</td>";
                            echo "<td>" . $row['year_level'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                        } else {
                            echo "<td>" . $row['user_id'] . "</td>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['role'] . "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="./instascan.min.js"></script>
<!-- Fetch and Render Chart -->
<script>
const cpidData = <?php echo json_encode($cpidData); ?>; // Dynamically fetched data for each CPID

// Dynamically create and render charts for each CPID
Object.keys(cpidData).forEach((cpid, index) => {
    // Create a container for the chart dynamically
    const chartDiv = document.createElement('div');
    chartDiv.id = `chartContainer${index}`;
    chartDiv.style.height = '370px';
    chartDiv.style.width = '30%';
    chartDiv.style.minWidth = '300px';
    chartDiv.style.margin = '10px';
    document.getElementById('chartsContainer').appendChild(chartDiv);

    // Get data for the current CPID
    const data = cpidData[cpid];

    // Render the chart
    const chart = new CanvasJS.Chart(chartDiv.id, {
        animationEnabled: true,
        title: {
            text: `${data.semester} Overview`, // Use semester name here dynamically
            fontSize: 20,
            fontColor: "#333",
        },
        data: [{
            type: "doughnut",
            startAngle: 240,
            innerRadius: "50%",
            yValueFormatString: "##0\"%\"",
            indexLabel: "{label}: {y}",
            dataPoints: [
                { y: data['Complete'], label: "Complete", color: "#800000" },
                { y: data['Pending'], label: "Pending", color: "#FFD700" }
            ]
        }]
    });

    chart.render();
});

// The following code remains unchanged
let lastScan = null;

function onScanSuccess(decodedText, decodedResult) {
    // Handle the scanned code
    const data = JSON.parse(decodedText);
    if (!data.user_id || !data.cpid) return;
    if (lastScan?.user_id == data.user_id && lastScan?.cpid == data.cpid) return;
    lastScan = data;
    // Redirect to page
    window.open(`./Admin_showresults.php?user_id=${data.user_id}&cpid=${data.cpid}`, '_blank');
}

function onScanFailure(error) {
    // Handle scan failure
}

let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: { width: 250, height: 250 } },
    false
);

function openQRModal() {
    document.getElementById('qrScanModal').style.display = 'flex'; // Show modal
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function closeQRModal() {
    document.getElementById('qrScanModal').style.display = 'none'; // Hide modal
    html5QrcodeScanner.clear();
    lastScan = null;
}

// Close modal when clicking outside of the modal content
window.onclick = function(event) {
    const modal = document.getElementById('qrScanModal');
    if (event.target === modal) {
        closeQRModal();
    }
};

// Confirmation dialog for logout
function confirmLogout() {
    return confirm("Are you sure you want to log out?");
}
</script>
