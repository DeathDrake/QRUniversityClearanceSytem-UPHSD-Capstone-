<?php
require_once '../Database/clearancedb.php';
session_start();

// TODO: actually check login status

// Ensure the user is logged in and has a user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the user_id from the session

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the form input
    $signatory_department = $_POST['signatory_department'];

    // Validate form inputs
    if (empty($signatory_department)) {
        echo "All fields are required.";
        exit();
    }

    $statement = $pdo->prepare('INSERT INTO signatory (signatory_department) VALUES (?)');
    $statement->execute([$signatory_department]);
    if ($statement->rowCount() < 1) {
        echo "Error creating signatory.";
        exit();
    }

    $signatory_id = $pdo->lastInsertId();

    $statement = $pdo->prepare('UPDATE admin SET signatory_id = ? WHERE user_id = ?');
    $statement->execute([$signatory_id, $user_id]);
    if ($statement->rowCount() < 1) {
        echo "Error updating admin with signatory.";
        exit();
    }

    header("Location: Admin_dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Admin Form</title>
    <link rel="stylesheet" href="Signatory_Form.css"> <!-- Make sure you have the correct CSS file -->
</head>
<body>
<div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="Admin_dashboard.php" class="button">Dashboard</a></li>
            <li><a href="Admin_Create_Account.php" class="button">Create Account</a></li>  
        <ul>               
    </div>

    <div class="container">
    <h1 class="form-title">Signatory Admin Form</h1>
    <form method="POST" action="Signatory_Form.php">
        <!-- Signatory Department -->
        <div class="input-group">
            <label for="signatory_department">Signatory Department</label>
            <select name="signatory_department" required>
                <option value="">Select Department</option>
                <option value="LIBRARY">LIBRARY</option>
                <option value="GUIDANCE">GUIDANCE</option>
                <option value="STUDENT AFFAIRS">STUDENT AFFAIRS</option>
                <option value="PREFECT OF DISCIPLINE">PREFECT OF DISCIPLINE</option>
                <option value="ALUMNI AFFAIRS">ALUMNI AFFAIRS (For Graduating Students only)</option>
                <option value="ACCOUNTING">ACCOUNTING</option>
                <option value="GRADUATION FEE">GRADUATION FEE (For Graduating Students only)</option>
                <option value="OFFICE OF INT'L STUDENT AFFAIRS">OFFICE OF INT'L STUDENT AFFAIRS</option>
                <option value="CAMPUS MINISTRY">CAMPUS MINISTRY (For International Students only)</option>
                <option value="SPORTS/ATHLETICS">SPORTS/ATHLETICS (For Athletes only)</option>
                <option value="DEAN">DEAN</option>
                <option value="UNIVERSITY REGISTRAR">UNIVERSITY REGISTRAR</option>
                <option value="SCHOOL DIRECTOR">SCHOOL DIRECTOR</option>
            </select>
        </div>

        <!-- Submit Button -->
        <input type="submit" class="btn" value="Create Signatory Admin">
    </form>
</div>

</body>
</html>
