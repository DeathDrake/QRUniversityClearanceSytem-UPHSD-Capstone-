<?php
require_once '../Database/clearancedb.php'; // Adjust the path to your database connection file

try {
    // Query to count total students
    $query = $pdo->query('SELECT COUNT(*) AS total_students FROM students');
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Return the total count as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'total_students' => $result['total_students']
    ]);
} catch (Exception $e) {
    // Handle errors
    echo json_encode([
        'error' => 'Failed to fetch data: ' . $e->getMessage()
    ]);
}
