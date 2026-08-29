<?php include_once "database_conn.php"; ?>

<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$teacherIDVal = $_POST['teacherID'];
	$passwordVal = $_POST['password'];


	//Check if teacher_id exists
	$stmt1 = $conn->prepare("SELECT teacher_id FROM teacher WHERE teacher_id = ?");
	$stmt1->bind_param("s", $teacherIDVal);
	$stmt1->execute();
	$result1 = $stmt1->get_result();

	//If not give the status notMatch then stop the script
	if ($result1->num_rows === 0) {
		echo json_encode(['status' => 'notMatch']);
		$stmt1->close();
		exit;
	}

	//get the password of the account
	$stmt2 = $conn->prepare("SELECT u.password, u.user_id FROM teacher t JOIN user u ON t.user_id = u.user_id WHERE teacher_id = ?");
	$stmt2->bind_param("s", $teacherIDVal);
	$stmt2->execute();
	$result2 = $stmt2->get_result();

	//If it returns a row
	if ($result2->num_rows > 0) {
		$row = $result2->fetch_assoc();
		$hashedPassword = $row['password'];

		//Get the hashed password then verify, if it fails verification then exit the script
		if (!password_verify($passwordVal, $hashedPassword)) {
            echo json_encode(['status' => 'wrongPass']);
            $stmt1->close();
			$stmt2->close();
            $conn->close();
            exit;
		}
		$_SESSION['teacherID'] = $teacherIDVal;
		$_SESSION['userID'] = $row['user_id'] ?? '';
		echo json_encode(['status' => 'success']);
	}  else {
		echo json_encode(['status' => 'invalid']);
	}




	$stmt1->close();
	$stmt2->close();
	$conn->close();
}
?>