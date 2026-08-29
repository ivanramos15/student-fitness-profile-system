<?php
session_start();
header('Content-Type: application/json');

include_once "database_conn.php";

$oldPassword = $_POST['old-password'] ?? null;
$user_id = $_SESSION['userID'] ?? null;

if (!$user_id || !$oldPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ? LIMIT 1");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($oldPassword, $row['password'])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'notMatch']);
    }
} else {
    echo json_encode(['status' => 'noUserFound']);
}

$stmt->close();
$conn->close();
?>