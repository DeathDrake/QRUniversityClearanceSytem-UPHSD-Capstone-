<?php
// Include the database connection (using PDO, like in index.php)
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in and is a Signatory Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    // Redirect to login if not logged in or not a Signatory Admin
    header('Location: ../index.php');
    exit();
}

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Fetch admin, user, and signatory data using PDO
$query = "
    SELECT 
        u.username, 
        a.first_name, 
        a.last_name, 
        a.email, 
        a.signatory_id, 
        s.signatory_department
    FROM user u
    JOIN admin a ON u.user_id = a.user_id
    LEFT JOIN signatory s ON a.signatory_id = s.signatory_id
    WHERE u.user_id = ?
";

// Prepare the query and execute it using PDO
$statement = $pdo->prepare($query);
$statement->execute([$user_id]);

// Fetch the data
$signatory_data = $statement->fetch(PDO::FETCH_ASSOC);

if (!$signatory_data) {
    die("Error fetching data.");
}

// Set the signatory_id in the session if not already set
if (!isset($_SESSION['signatory_id'])) {
    $_SESSION['signatory_id'] = $signatory_data['signatory_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Dashboard</title>
    <link rel="stylesheet" href="Signatory_dashboard.css">
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
        <h1>Signatory Admin Information</h1>
        <form>
            <!-- Display user table information -->
            <label>Username:</label>
            <input type="text" value="<?php echo htmlspecialchars($signatory_data['username']); ?>" readonly>

            <br><br>

            <!-- Display admin table information -->
            <label>First Name:</label>
            <input type="text" value="<?php echo htmlspecialchars($signatory_data['first_name']); ?>" readonly>

            <label>Last Name:</label>
            <input type="text" value="<?php echo htmlspecialchars($signatory_data['last_name']); ?>" readonly>

            <label>Email:</label>
            <input type="text" value="<?php echo htmlspecialchars($signatory_data['email']); ?>" readonly>

            <!-- Display signatory table information -->
            <label>Signatory Department:</label>
            <input type="text" value="<?php echo htmlspecialchars($signatory_data['signatory_department']); ?>" readonly>
        </form>
    </div>
</body>
</html>
