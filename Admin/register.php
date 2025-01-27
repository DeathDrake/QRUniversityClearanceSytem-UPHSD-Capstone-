<?php
require_once '../Database/clearancedb.php';
session_start();

if (isset($_POST['signUp'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // ALWAYS FUCKING HASH THE PASSWORD!!!!!!!!
    $role = $_POST['role']; // Capture the role
    $admin_type = isset($_POST['admin_type']) ? $_POST['admin_type'] : ''; // Capture the admin type (if applicable)

    // Check if the username already exists using a prepared statement
    $statement = $pdo->prepare('SELECT * FROM user WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();
    if ($user) {
        // Set the error message in the session if the username exists
        $_SESSION['error'] = "Username already exists!";
        header("Location: Admin_Create_Account.php"); // Redirect back to the registration page
        exit();
    }

    // Directly insert the plain text password (without hashing) THIS IS FUCKING STUPID

    $statement = $pdo->prepare('INSERT INTO user (username, password, role) VALUES (?, ?, ?)');
    $statement->execute([$username, $password, $role]);

    if ($statement->rowCount() < 1) {
        $_SESSION['error'] = "Error: " . $insertQuery->error;
    } else {
        $user_id = $pdo->lastInsertId();
        // Check if the selected role is 'admin'
        if ($role == 'admin') {
            // Store the user_id in the session to pass it to the next page
            $_SESSION['user_id'] = $user_id;

            // Check the admin type and redirect accordingly
            if ($admin_type == 'Signatory Admin') {
                // Redirect to Signatory_Form.php
                header("Location: Signatory_Form.php");
                exit();
            }

            if ($admin_type == 'Super Admin') {
                // Redirect directly to Admin_dashboard.php for Super Admin
                header("Location: Admin_dashboard.php");
                exit();
            }

            // Redirect to Admin_Creation_Form.php for other admin types (if applicable)
            header("Location: Admin_Creation_Form.php");
            exit();
        } else if ($role == 'student') {
            // Redirect to the student creation form and pass the user_id
            header("Location: Student_Creation_Form.php?user_id=$user_id");
            exit();
        }
    }
}

// Redirect back to the registration page if there's an error
if (isset($_SESSION['error'])) {
    header("Location: Admin_Create_Account.php");
    exit();
}
?>
