<?php include_once "database_conn.php"; ?>

<?php

$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$dateOfBirth = $_POST['dateOfBirth'] ?? '';
$gender = $_POST['gender'] ?? '';
$section = $_POST['section'] ?? '';
$password = $_POST['password'] ?? '';

$parts = explode('/', $section);

$program_id = trim($parts[0] ?? '');
$section_id = trim($parts[1] ?? '');

$fields = [
    'first name' => $firstName,
    'last name' => $lastName,
    'date of birth' => $dateOfBirth,
    'gender' => $gender,
    'section' => $section,
    'password' => $password,
];

$missing = [];
foreach ($fields as $fieldName => $fieldValue) {
    if ($fieldValue === '') {
        $missing[] = $fieldName;
    }
}

if ($missing || count($parts) !== 2 || $section_id === '') {
    echo "<div class='confirm-div'>
	<p class='title-text'>Please fill out every field.</p>
	<a href='signUp.php'><button class='btn btn_back'>Go Back</button></a></div>";
	exit;
}

$stmtCheck = $conn->prepare("SELECT section_id FROM section WHERE section_id = ? AND program_id = ?");
$stmtCheck->bind_param("ss", $section_id, $program_id);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows === 0) {
	echo "<div class='confirm-div'>
	<p class='title-text'>That section does not exist.</p>
	<a href='signUp.php'><button class='btn btn_back'>Go Back</button></a></div>";
	exit;
}
$stmtCheck->close();


// add user

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$sqlAddUser = "INSERT INTO user (`password`, `role`) VALUES (?, 'ROLE-0001')";
$stmt1 = $conn->prepare($sqlAddUser);
$stmt1->bind_param("s", $hashedPassword);

if ($stmt1->execute()) {
	$sqlFindUser = "SELECT user_id FROM `user` ORDER BY user_id DESC LIMIT 1";

	$result1 = $conn->query($sqlFindUser);
	$userValue = $result1->fetch_assoc();
	$latestUserID = $userValue['user_id'];

	// add student
	$sqlAddStudent = "INSERT INTO student (`user_id`, `first_name`, `last_name`, `student_sex`, `date_of_birth`) VALUES (?, ?, ?, ?, ?)";

	$stmt2 = $conn->prepare($sqlAddStudent);
	$stmt2->bind_param("sssss", $latestUserID, $firstName, $lastName, $gender, $dateOfBirth);

	if ($stmt2->execute()) {

		$query = "SELECT student_id FROM `student` ORDER BY student_id DESC LIMIT 1;";
		$result = $conn->query($query);
		$resultValue = $result->fetch_assoc();

		$studentID = $resultValue['student_id'];

		// add student section in student_section
		$sqlSection = "INSERT INTO student_section (`student_id`, `section_id`, `program_id`) VALUES (?, ?, ?)";

		$stmt3 = $conn->prepare($sqlSection);
		$stmt3->bind_param("sss", $studentID, $section_id, $program_id);
		if ($stmt3->execute()) {
			echo "<a href='login.html'><button class='btn btn_back'>Go Back</button></a>
			<div class='confirm-div'>
				<p class='title-text'>You're all set! Account created.</p>
			";

			echo "<p class='studentID-text'>Student ID: <span>$studentID</span></p>
		<p class='note-text'>Note: Please remember your student ID as you log in.</p></div>";
		} else {
			$cleanupStudent = $conn->prepare("DELETE FROM student WHERE student_id = ?");
			$cleanupStudent->bind_param("s", $studentID);
			$cleanupStudent->execute();
			$cleanupStudent->close();

			$cleanupUser = $conn->prepare("DELETE FROM user WHERE user_id = ?");
			$cleanupUser->bind_param("s", $latestUserID);
			$cleanupUser->execute();
			$cleanupUser->close();

			echo "<div class='confirm-div'>
	<p class='title-text'>Error adding account. Please try again.</p>
	<a href='signUp.php'><button class='btn btn_back'>Go Back</button></a></div>";
		}
	} else {
		$cleanupUser = $conn->prepare("DELETE FROM user WHERE user_id = ?");
		$cleanupUser->bind_param("s", $latestUserID);
		$cleanupUser->execute();
		$cleanupUser->close();

		echo "<div class='confirm-div'>
	<p class='title-text'>Error adding account. Please try again.</p>
	<a href='signUp.php'><button class='btn btn_back'>Go Back</button></a></div>";
	}
} else {
	echo "<div class='confirm-div'>
	<p class='title-text'>Error adding account. Please try again.</p>
	<a href='signUp.php'><button class='btn btn_back'>Go Back</button></a></div>";
}










//you have to insert a user with the role and the password
//This is the query for it
//INSERT INTO `user`(`password`, `role`) VALUES (?,?);




// $stmt->close();
// $conn->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Welcome!</title>
	<link rel="stylesheet" href="css/general.css" />
	<link rel="stylesheet" href="css/signUp.css" />
</head>

<body>

</body>

</html>