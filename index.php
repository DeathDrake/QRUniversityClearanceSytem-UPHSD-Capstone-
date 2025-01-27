<?php
// Include the database connection
require_once 'Database/clearancedb.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to get the user with case-sensitive comparison
    $statement = $pdo->prepare('SELECT * FROM user WHERE BINARY username = ? LIMIT 1');
    $statement->execute([$username]);
    $user = $statement->fetch();

    if (!$user) {
        $error = "Invalid username or password.";
    } else if (!password_verify($password, $user['password'])) {
        // Using password_verify to ensure password matches if it's hashed
        $error = "Invalid password.";
    } else {
        // Store session data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $role = $user['role'];

        if ($role == 'admin') {
            // Check if the admin is a Signatory Admin or Super Admin
            $admin_statement = $pdo->prepare('SELECT * FROM admin WHERE user_id = ? LIMIT 1');
            $admin_statement->execute([$user['user_id']]);
            $admin = $admin_statement->fetch();

            if ($admin) {
                $admin_type = $admin['type']; // Get the type (Signatory Admin or Super Admin)

                // Store the role in session and redirect based on admin type
                $_SESSION['role'] = $admin_type;

                if ($admin_type == 'Super Admin') {
                    header("Location: Admin/Admin_dashboard.php");
                    exit();
                } elseif ($admin_type == 'Signatory Admin') {
                    header("Location: Departmentdashboard/Signatory_Dashboard.php");
                    exit();
                } else {
                    $error = "Invalid admin type.";
                }
            } else {
                $error = "No admin found for this user.";
            }
        } elseif ($role == 'student') {
            // If the user is a student, store role in session and redirect
            $_SESSION['role'] = 'student';
            header("Location: students/students_clearanceform.php");
            exit();
        } else {
            $error = "Invalid role.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Main Form</title>
    <link rel="stylesheet" href="index.css">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar">
        <img src="images/perpetualsmallicon.png" alt="Icon" class="navbar-icon">
    </nav>

    <!-- Main Login Section -->
    <div class="login-container">
        <div class="login">
            <!-- Logo Header -->
            <div class="logo-header">
                <img src="images/perpetualicon.png" alt="University Logo">
            </div>

            <!-- Login Form -->
            <form action="" method="POST">
                <div class="input-wrapper">
                    <i class="fas fa-user icon"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Username" 
                        required>
                </div>
                <div class="input-wrapper">
                    <i class="fas fa-lock icon"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Password" 
                        required>
                </div>
                <input 
                    type="submit" 
                    class="btn" 
                    value="Login">
            </form>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navbar -->
    <nav class="bottom-navbar"></nav>
</body>
</html>
