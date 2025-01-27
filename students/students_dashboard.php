<?php
// Include your database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and student data using a JOIN query
$statement = $pdo->prepare('
    SELECT u.username, s.StudNo, s.fname, s.mname, s.lname, s.course, s.year_level
    FROM user u
    JOIN students s ON u.user_id = s.user_id
    WHERE u.user_id = ?
');
$statement->execute([$user_id]);
$student = $statement->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch updated data from POST request
    $updated_username = $_POST['username'];
    $updated_fname = $_POST['fname'];
    $updated_mname = $_POST['mname'];
    $updated_lname = $_POST['lname'];
    $updated_course = $_POST['course'];
    $updated_year_level = $_POST['year_level'];

    // Prepare an SQL statement to update the student record
    $update_statement = $pdo->prepare('
        UPDATE students
        SET fname = ?, mname = ?, lname = ?, course = ?, year_level = ?
        WHERE user_id = ?
    ');
    $update_statement->execute([$updated_fname, $updated_mname, $updated_lname, $updated_course, $updated_year_level, $user_id]);

    // Optionally update the username in the user table
    $update_username_statement = $pdo->prepare('
        UPDATE user
        SET username = ?
        WHERE user_id = ?
    ');
    $update_username_statement->execute([$updated_username, $user_id]);

    // Redirect to avoid resubmitting the form on page refresh
    header('Location: students_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            min-height: 100vh;
            background: #f5f5f7; /* Light gray MacOS-like background */
            color: #333;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background: maroon; /* Dark gray for sidebar */
            color: white;
            padding: 30px 20px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        .sidebar h2.logo {
            font-size: 18px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 40px;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebar ul li a:hover {
            background: rgba(234, 234, 234, 0.8); /* Hover effect for links */
        }

        .sidebar ul li a i {
            font-size: 18px;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 270px;
            padding: 40px;
            width: calc(100% - 270px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            min-height: 100vh;
        }

        h1 {
            margin-bottom: 20px;
            font-weight: bold;
            color: #1c1c1e;
        }

        form {
            background: #f5f5f7;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
            transition: border-color 0.3s ease;
        }

        form input:focus {
            border-color: #007aff; /* Blue focus border for MacOS feel */
            outline: none;
        }

        form button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        #edit-button {
            background: #007aff; /* Blue button for editing */
            color: white;
        }

        #edit-button:hover {
            background: #005bb5;
        }

        #save-button {
            background: #34c759; /* Green save button */
            color: white;
            display: none; /* Initially hidden */
        }

        #save-button:hover {
            background: #28a745;
        }

        /* Modal Styling */
        #confirmation-modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            width: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .modal-content p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-between;
        }

        .modal-buttons button {
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        #confirm-btn {
            background: #34c759; /* Green button */
            color: white;
        }

        #confirm-btn:hover {
            background: #28a745;
        }

        #cancel-btn {
            background: #ccc; /* Gray cancel button */
            color: #333;
        }

        #cancel-btn:hover {
            background: #aaa;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Student Dashboard</h2>
        <ul>
            <li><a href="students_dashboard.php"><i class="fas fa-user"></i> Account Profile</a></li>
            <li><a href="students_clearanceform.php"><i class="fas fa-file-alt"></i> Clearance Form</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1>Student Account</h1>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="editable" readonly>

            <label for="StudNo">Student Number:</label>
            <input type="text" id="StudNo" name="StudNo" value="<?= htmlspecialchars($student['StudNo']) ?>" readonly>

            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($student['fname']) ?>" class="editable" readonly>

            <label for="mname">Middle Name:</label>
            <input type="text" id="mname" name="mname" value="<?= htmlspecialchars($student['mname']) ?>" class="editable" readonly>

            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($student['lname']) ?>" class="editable" readonly>

            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="<?= htmlspecialchars($student['course']) ?>" class="editable" readonly>

            <label for="year_level">Year Level:</label>
            <input type="text" id="year_level" name="year_level" value="<?= htmlspecialchars($student['year_level']) ?>" class="editable" readonly>

            <button type="button" id="edit-button" onclick="enableEditing()">Edit</button>
            <button type="submit" id="save-button" onclick="openModal(event)">Save</button>
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal">
        <div class="modal-content">
            <p>Are you sure you want to save the changes?</p>
            <div class="modal-buttons">
                <button id="confirm-btn" onclick="confirmSave()">Yes</button>
                <button id="cancel-btn" onclick="closeModal()">No</button>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to enable and disable editing
        function enableEditing() {
            document.querySelectorAll('.editable').forEach(input => input.removeAttribute('readonly'));
            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('save-button').style.display = 'inline-block';
        }

        function openModal(event) {
            event.preventDefault();
            document.getElementById('confirmation-modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('confirmation-modal').style.display = 'none';
        }

        function confirmSave() {
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>
