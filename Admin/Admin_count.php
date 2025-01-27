<?php
require_once '../Database/clearancedb.php';
session_start(); // Start the session to use session variables

$cpid = 1;

// Fetch clearance details for the given Cpid
$statement = $pdo->prepare('SELECT DISTINCT student_id, status FROM clearance WHERE Cpid = ?');
$statement->execute([$cpid]);
$clearances = $statement->fetchAll();

$students = [];

// Process clearance statuses
foreach ($clearances as $clearance) {
    $id = $clearance['student_id'];
    $status = $clearance['status'];
    if (empty($students[$id])) {
        $students[$id] = $status === 'Signed';
    } else if ($students[$id] === true && $status !== 'Signed') {
        $students[$id] = $status === 'Signed';
    }
}

$signedCount = 0;
$pendingCount = 0;

// Count signed and pending statuses
foreach ($students as $signed) {
    if ($signed) {
        $signedCount++;
    } else {
        $pendingCount++;
    }
}

// Output JSON for use in dashboards or APIs
header('Content-Type: application/json');
echo json_encode([
    'signed' => (int)$signedCount,
    'pending' => (int)$pendingCount,
]);
