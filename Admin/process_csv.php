<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if a file is uploaded
if (!isset($_FILES['csv']['tmp_name']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    die('No file uploaded or file upload error.');
}

// Validate that the uploaded file is a CSV
$file = $_FILES['csv']['tmp_name'];
$fileType = mime_content_type($file);
if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
    die('Invalid file format. Please upload a valid CSV file.');
}

// Function to process user and student data
function processUserAndStudent($pdo, $username, $rawPassword, $role, $studNo, $fname, $mname, $lname, $email, $course, $yearLevel) {
    $password = password_hash($rawPassword, PASSWORD_BCRYPT);

    // Check if the username already exists using a prepared statement
    $statement = $pdo->prepare('SELECT * FROM user WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();

    if ($user) {
        // If the user exists, we can update or ignore this entry
        return;
    }

    // Insert into the `user` table
    $userInsertStmt = $pdo->prepare('INSERT INTO user (username, password, role) VALUES (?, ?, ?)');
    $userInsertStmt->execute([$username, $password, $role]);

    // Get the last inserted user_id
    $userId = $pdo->lastInsertId();

    // If role is 'student', insert into the `students` table
    if ($role === 'student') {
        $studentInsertStmt = $pdo->prepare('
            INSERT INTO students (user_id, username, StudNo, fname, mname, lname, email, course, year_level) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $studentInsertStmt->execute([$userId, $username, $studNo, $fname, $mname, $lname, $email, $course, $yearLevel]);
    }
}

// Open and process the CSV file
if (($handle = fopen($file, "r")) !== FALSE) {
    // Skip the first row if it contains headers
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Process each row of the CSV
        $username = $data[0];
        $rawPassword = $data[1];
        $role = $data[2];
        $studNo = $data[3] ?? null;
        $fname = $data[4] ?? null;
        $mname = $data[5] ?? null;
        $lname = $data[6] ?? null;
        $email = $data[7] ?? null;
        $course = $data[8] ?? null;
        $yearLevel = $data[9] ?? null;

        processUserAndStudent($pdo, $username, $rawPassword, $role, $studNo, $fname, $mname, $lname, $email, $course, $yearLevel);
    }
    fclose($handle);
}

unlink($file); // Delete the file after processing

// Redirect to Admin_Create_Account.php
header('Location: Admin_Create_Account.php');
exit;
?>
