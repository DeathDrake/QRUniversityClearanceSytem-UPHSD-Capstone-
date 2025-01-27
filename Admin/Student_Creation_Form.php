<?php 
require_once '../Database/clearancedb.php';
session_start();

// Check if the user ID is provided
$user_id = $_GET['user_id'];
if (!isset($user_id) || empty($user_id)) {
    header("Location: ../index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch the username for the provided user_id from the user table
$statement = $pdo->prepare('SELECT username FROM user WHERE user_id = ?');
$statement->execute([$user_id]);
$username = $statement->fetchColumn();

if (!$username) {
    echo "Error: User ID not found or username missing.";
    exit();
}

if (isset($_POST['createStudent'])) {
    $stud_no = $_POST['stud_no']; // New field for Student Number
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    // Insert into students table, including the username
    $statement = $pdo->prepare('INSERT INTO students (user_id, username, StudNo, fname, mname, lname, email, course, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->execute([$user_id, $username, $stud_no, $first_name, $middle_name, $last_name, $email, $course, $year_level]);

    if ($statement->rowCount() < 1) {
        echo "Error creating student.";
        exit();
    }
    header("Location: Admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Creation Form</title>
    <link rel="stylesheet" href="Student_Creation_Form.css"> <!-- Ensure correct CSS file path -->
</head>
<body>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="Admin_dashboard.php" class="button">Dashboard</a></li>
            <li><a href="Admin_Create_Account.php">Create Account</a></li>
        </ul>
    </div>

    <!-- Main Content Section -->
    <div class="container" id="signup">
        <h1 class="form-title">Student Creation Form</h1>
        <form method="post" action="">
            <!-- Student Number (StudNo) Input Field -->
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="stud_no" placeholder="Enter Student Number" required>
                <label for="stud_no">Student Number</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="first_name" placeholder="Enter first name" required>
                <label for="first_name">First Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="middle_name" placeholder="Enter middle name">
                <label for="middle_name">Middle Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="last_name" placeholder="Enter last name" required>
                <label for="last_name">Last Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-book"></i>
                
                <select name="course" required>
                <option value="" disabled selected>Select a course</option>
                    <option value="BACHELOR OF ELEMENTARY EDUCATION">BACHELOR OF ELEMENTARY EDUCATION</option>
                    <option value="BACHELOR OF ARTS">BACHELOR OF ARTS</option>
                    <option value="BACHELOR OF ARTS IN COMMUNICATION">BACHELOR OF ARTS IN COMMUNICATION</option>
                    <option value="BACHELOR OF ARTS IN PSYCHOLOGY">BACHELOR OF ARTS IN PSYCHOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN ACCOUNTANCY">BACHELOR OF SCIENCE IN ACCOUNTANCY</option>
                    <option value="BACHELOR OF SCIENCE IN ACCOUNTING TECHNOLOGY">BACHELOR OF SCIENCE IN ACCOUNTING TECHNOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN ARCHITECTURE">BACHELOR OF SCIENCE IN ARCHITECTURE</option>
                    <option value="BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION">BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION</option>
                    <option value="BACHELOR OF SCIENCE IN CIVIL ENGINEERING">BACHELOR OF SCIENCE IN CIVIL ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN CIVIL ENGINEERING (STRUCTURAL ENGINEERING)">BACHELOR OF SCIENCE IN CIVIL ENGINEERING (STRUCTURAL ENGINEERING)</option>
                    <option value="BACHELOR OF SCIENCE IN COMMERCE">BACHELOR OF SCIENCE IN COMMERCE</option>
                    <option value="BACHELOR OF SCIENCE IN COMPUTER ENGINEERING">BACHELOR OF SCIENCE IN COMPUTER ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN COMPUTER SCIENCE">BACHELOR OF SCIENCE IN COMPUTER SCIENCE</option>
                    <option value="BACHELOR OF SCIENCE IN COMPUTER SCIENCE WITH BPO">BACHELOR OF SCIENCE IN COMPUTER SCIENCE WITH BPO</option>
                    <option value="BACHELOR OF SCIENCE IN CRIMINOLOGY">BACHELOR OF SCIENCE IN CRIMINOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING">BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN ELECTRONICS AND COMMUNICATIONS ENGINEERING">BACHELOR OF SCIENCE IN ELECTRONICS AND COMMUNICATIONS ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING">BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN ENTREPRENEURSHIP">BACHELOR OF SCIENCE IN ENTREPRENEURSHIP</option>
                    <option value="BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT">BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT</option>
                    <option value="BACHELOR OF SCIENCE IN HOTEL AND RESTAURANT MANAGEMENT">BACHELOR OF SCIENCE IN HOTEL AND RESTAURANT MANAGEMENT</option>
                    <option value="BACHELOR OF SCIENCE IN INDUSTRIAL ENGINEERING">BACHELOR OF SCIENCE IN INDUSTRIAL ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY">BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH BPO">BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH BPO</option>
                    <option value="BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH SPECIALIZATION IN GAME DEVELOPMENT">BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY WITH SPECIALIZATION IN GAME DEVELOPMENT</option>
                    <option value="BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING">BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING</option>
                    <option value="BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY">BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN NURSING">BACHELOR OF SCIENCE IN NURSING</option>
                    <option value="BACHELOR OF SCIENCE IN OCCUPATIONAL THERAPY">BACHELOR OF SCIENCE IN OCCUPATIONAL THERAPY</option>
                    <option value="BACHELOR OF SCIENCE IN PHARMACY">BACHELOR OF SCIENCE IN PHARMACY</option>
                    <option value="BACHELOR OF SCIENCE IN PHYSICAL THERAPY">BACHELOR OF SCIENCE IN PHYSICAL THERAPY</option>
                    <option value="BACHELOR OF SCIENCE IN RADIOLOGIC TECHNOLOGY">BACHELOR OF SCIENCE IN RADIOLOGIC TECHNOLOGY</option>
                    <option value="BACHELOR OF SCIENCE IN TOURISM">BACHELOR OF SCIENCE IN TOURISM</option>
                    <option value="BACHELOR OF SCIENCE IN TOURISM MANAGEMENT">BACHELOR OF SCIENCE IN TOURISM MANAGEMENT</option>
                    <option value="BACHELOR OF SECONDARY EDUCATION">BACHELOR OF SECONDARY EDUCATION</option>
                </select>
            </div>

            <div class="input-group">
                <i class="fas fa-graduation-cap"></i>
               
                <select name="year_level" required>
                    <option value="" disabled selected>Select Year Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <input type="submit" class="btn" value="Create Account" name="createStudent">
        </form>
    </div>
</body>
</html>
