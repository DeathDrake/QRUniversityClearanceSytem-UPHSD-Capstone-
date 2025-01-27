<?php
require_once '../Database/clearancedb.php';
session_start(); // Start the session to use session variables

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Retrieve error message if it exists
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['error']); // Clear the error message after displaying it

// Fetch the logged-in user's email from the database
$user_id = $_SESSION['user_id'];
$statement = $pdo->prepare('SELECT email FROM admin WHERE user_id = ?');
$statement->execute([$user_id]);
$email = $statement->fetchColumn();

// Store the fetched email in the session
$_SESSION['user_email'] = $email; // Now you can use this throughout your application

// Get the selected directory type from the form (default to 'user')
$directoryType = isset($_POST['directoryType']) ? $_POST['directoryType'] : 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Admin_Create_Account.css">
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
<script>
    // Confirmation dialog for logout
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }

    function showFileName(event) {
        const fileInput = event.target;
        const fileName = fileInput.files[0] ? fileInput.files[0].name : "Drag & Drop your CSV file here or click to upload";
        document.getElementById("fileName").textContent = fileName;
    }

</script>
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
    <div class="sidebar-bottom">
    <a href="../index.php" onclick="return confirmLogout();" class="button">
    <i class="fas fa-sign-out-alt"></i> Log Out
        </a>

            <p>Logged in as: <?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>
    </div>

</div>
</div>

<!-- Registration Form -->
<div class="container" id="signup">
    <h1 class="form-title">Create Account</h1>
    <form method="post" action="register.php">
        <!-- Username Field -->
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <!-- Password Field -->
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <!-- Role Dropdown -->
        <div class="input-group">
            <i class="fas fa-user-tag"></i>
            <select name="role" id="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="admin">Admin</option>
                <option value="signatory">Signatory</option>
                <option value="student">Student</option>
            </select>
        </div>

        <input type="submit" class="btn" value="Sign Up" name="signUp">
    </form>

<!-- Drag & Drop CSV Upload Box -->
<form method="post" action="process_csv.php" enctype="multipart/form-data">
    <div class="file-upload-container">
        <div class="file-upload" id="fileUploadBox">
            <input class="file-input" id="csv" type="file" name="csv" accept=".csv" required onchange="showFileName(event)">
            <label class="file-label" for="csv">
                <i class="upload-icon">📁</i>
                <p id="fileName">Drag & Drop your CSV file here or click to upload</p>
            </label>
        </div>
    </div>
    <input type="submit" class="btn" value="Process CSV" name="submit">
</form>

<!-- Display error message if it exists -->
<?php if ($errorMessage): ?>
    <div class="error-message" style="color: red; margin-top: 10px;"><?php echo $errorMessage; ?></div>
<?php endif; ?>
</div>
</body>
</html>
