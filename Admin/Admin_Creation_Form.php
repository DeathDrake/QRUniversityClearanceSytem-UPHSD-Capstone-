<?php
require_once '../Database/clearancedb.php';

session_start();

// TODO: actually check login status

// Ensure that the user is logged in and the user_id is passed in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: No user ID found. Please register first.";
    exit();
}

if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the user_id from the session

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form inputs
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $type = $_POST['type']; // Either 'Super Admin' or 'Signatory Admin'

    // Validate form inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($type)) {
        echo "All fields are required.";
        exit();
    }

    $statement = $pdo->prepare('INSERT INTO admin (user_id, first_name, last_name, email, type) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$user_id, $first_name, $last_name, $email, $type]);
    if ($statement->rowCount() < 1) {
        echo "Error creating admin.";
        exit();
    }

    if ($type == 'Signatory Admin') {
        // Redirect to the Signatory Form for additional information
        header("Location: Signatory_Form.php");
        exit();
    } else if ($type == 'Super Admin') {
        // Redirect to the Admin Dashboard for Super Admins
        header("Location: Admin_dashboard.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin</title>
    <link rel="stylesheet" href="Admin_Creation_Form.css"> <!-- Ensure the CSS file is properly linked -->
</head>
<body>
    <!-- Sidebar Section -->
    <div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="Admin_dashboard.php" class="button">Dashboard</a></li>
            <li><a href="Admin_Create_Account.php" class="button">Create Account</a></li>
        </ul>
    </div>

    <!-- Main Content Section -->
    <div class="container" id="signup">
        <h1 class="form-title">Admin Creation Form</h1>
        <form method="POST" action="Admin_Creation_Form.php">
            <!-- First Name -->
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="first_name" placeholder="Enter First Name" required>
                <label for="first_name">First Name</label>
            </div>

            <!-- Last Name -->
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="last_name" placeholder="Enter Last Name" required>
                <label for="last_name">Last Name</label>
            </div>

            <!-- Email -->
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter Email" required>
                <label for="email">Email</label>
            </div>

            <!-- Admin Type Dropdown -->
            <div class="input-group">
                <i class="fas fa-users"></i>
                <select name="type" required>
                    <option value="">Select Admin Type</option>
                    <option value="Super Admin">Super Admin</option>
                    <option value="Signatory Admin">Signatory Admin</option>
                </select>
                <label for="type">Admin Type</label>
            </div>

            <!-- Submit Button -->
            <input type="submit" class="btn" value="Create Admin">
        </form>
    </div>
</body>
</html>
