<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the logged-in user's email if it's not already stored in the session
if (!isset($_SESSION['user_email'])) {
    $user_id = $_SESSION['user_id'];
    $statement = $pdo->prepare("SELECT email FROM admin WHERE user_id = ?");
    $statement->execute([$user_id]);
    $email = $statement->fetchColumn();
    $_SESSION['user_email'] = $email; // Store the email in the session
} else {
    $email = $_SESSION['user_email'];
}

// Handle form submission to add a new clearance period
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $clearancetype = $_POST['clearancetype']; // Fetch the Clearance Type

    $statement = $pdo->prepare("INSERT INTO clearance_period (school_year, semester, startdate, enddate, clearancetype) VALUES (?, ?, ?, ?, ?)");
    $statement->execute([$school_year, $semester, $start_date, $end_date, $clearancetype]);

    // Redirect to refresh the page and show updated table
    header("Location: Create_Clearanceform.php");
    exit();
}

// Fetch all clearance periods with the number of students in Complete and Pending statuses
$statement = $pdo->prepare("
    SELECT cp.Cpid, cp.school_year, cp.semester, cp.startdate, cp.enddate, cp.clearancetype,
           SUM(c.status = 'Complete') AS complete_count,
           SUM(c.status = 'Pending') AS pending_count
    FROM clearance_period cp
    LEFT JOIN clearance c ON cp.Cpid = c.Cpid
    GROUP BY cp.Cpid
");
$statement->execute();
$clearance_periods = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Clearance Form</title>
    <link rel="stylesheet" href="Admin_dashboard.css">
    <link rel="stylesheet" href="Create_Clearanceform.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   
    <style>
        /* Ensure the sidebar stretches vertically */
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
    <img src="/images/perpetualsmallicon.png" alt="Perpetual Logo" class="logo-image">
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
            <a href="../logout.php" class="button">
            <i class="fas fa-sign-out-alt"></i> Log Out
    </a>
            <p>Logged in as: <?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>
</div>

    <div class="main-content">
        <div class="form-container">
            <h2>Create Clearance Period</h2>
            <form method="POST" action="Create_Clearanceform.php">
                <label for="school_year">School Year</label>
                <select id="school_year" name="school_year" required>
                    <option value="" disabled selected>Select School Year</option>
                    <?php
                        $currentYear = date("Y");
                        for ($i = 0; $i < 3; $i++) {
                            $startYear = $currentYear + $i;
                            $endYear = $startYear + 1;
                            echo "<option value='{$startYear}-{$endYear}'>{$startYear}-{$endYear}</option>";
                        }
                    ?>
                </select>

                <label for="semester">Semester</label>
                <select id="semester" name="semester" required>
                    <option value="" disabled selected>Select Semester</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="3rd Semester">3rd Semester</option>
                    <option value="4th Semester">Summer</option>
                </select>

                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" required>

                <div class="clearance-form">
    <form method="post" action="your-action-url.php">
        <label for="clearancetype">Clearance Form Type</label>
        <select id="clearancetype" name="clearancetype" required>
            <option value="" disabled selected>Select Type</option>
            <option value="Graduating">Graduating</option>
            <option value="Non-Graduating">Non-Graduating</option>
        </select>
        <button type="submit" class="btn btn-add">Add Clearance Period</button>
    </form>
</div>


        <div class="table-container">
            <h2>Clearance Record</h2>
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Clearance Period</th>
                        <th>Clearance Type</th>
                        <th>Details</th>
                        <th>Completion Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearance_periods as $period): ?>
                        <tr>
                            <td><?php echo $period['school_year']; ?></td>
                            <td><?php echo $period['semester']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($period['startdate'])) . " - " . date('M d, Y', strtotime($period['enddate'])); ?></td>
                            <td><?php echo htmlspecialchars($period['clearancetype']); ?></td>
                            <td><a href="ClearanceStudentsAdm.php?cpid=<?php echo $period['Cpid']; ?>">View Details</a></td>
                            <td>
                                Complete: <?php echo $period['complete_count'] ?: 0; ?>, 
                                Pending: <?php echo $period['pending_count'] ?: 0; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
