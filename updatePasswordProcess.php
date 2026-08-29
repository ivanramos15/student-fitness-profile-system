<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

include_once "database_conn.php";

$oldPassword = $_POST['old-password'] ?? null;
$newPassword = $_POST['new-password-confirm'] ?? null;
$user_id = $_SESSION['userID'] ?? null;

if (!$user_id || !$oldPassword || !$newPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

// Fetch current hashed password
$stmt1 = $conn->prepare("SELECT password FROM user WHERE user_id = ? LIMIT 1");
$stmt1->bind_param("s", $user_id);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows === 1) {
    $row = $result1->fetch_assoc();

    // Verify old password
    if (password_verify($oldPassword, $row['password'])) {
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $stmt2 = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
        $stmt2->bind_param("ss", $hashedPassword, $user_id);

        if ($stmt2->execute()) {
            
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
        }

        $stmt2->close();
    } else {
        echo json_encode(['status' => 'notMatch']);
    }
} else {
    echo json_encode(['status' => 'noUserFound']);
}

$stmt1->close();
$conn->close();
exit;
?>
