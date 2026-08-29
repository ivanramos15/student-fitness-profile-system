<?php
session_start();
$sessionUser = $_SESSION['userID'] ?? null;
if (!$sessionUser) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}
include_once "database_conn.php";

try {
    $userID = $sessionUser;
    $remark = $_POST['remark'];

    // Validate user exists
    $userCheck = $conn->prepare("SELECT user_id FROM user WHERE user_id = ?");
    $userCheck->bind_param("s", $userID);
    $userCheck->execute();
    $userCheck->store_result();

    if ($userCheck->num_rows === 0) {
        throw new Exception("Invalid user ID");
    }
    $userCheck->close();

    // Insert the remark into activity_log
    $insertQuery = "INSERT INTO activity_log (user_id, remark) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $userID, $remark);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        // Success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Remark created successfully',
            'log_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception("Failed to create remark");
    }

    $stmt->close();
} catch (Exception $e) {
    // Error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>