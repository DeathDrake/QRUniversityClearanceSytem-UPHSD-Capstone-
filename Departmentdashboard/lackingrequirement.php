<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notes'])) {
    foreach ($_POST['notes'] as $id => $note) {
        // Fetch current lackingrequirement value
        $stmt = $pdo->prepare("SELECT lackingrequirement FROM studentlackingrequirements WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Concatenate the note with the existing lackingrequirement
            $updatedLackingRequirement = $row['lackingrequirement'] . " - [Note: " . $note . "]";

            // Update the lackingrequirement field in the database
            $updateStmt = $pdo->prepare("UPDATE studentlackingrequirements SET lackingrequirement = :updatedLackingRequirement WHERE id = :id");
            $updateStmt->execute([
                ':updatedLackingRequirement' => $updatedLackingRequirement,
                ':id' => $id
            ]);
        }
    }
    echo "Notes saved successfully!";
}
?>
