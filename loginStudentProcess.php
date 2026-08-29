<?php
session_start();
include_once "database_conn.php";
//Added proper session handling in the student, consider adding this in the teacher side
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentIDVal = $_POST['studentID'];
    $passwordVal = $_POST['password'];

    
    //This might cause an error if the inputted student is not in the student table
	$stmt1 = $conn->prepare("SELECT student_id FROM student WHERE student_id = ?");
	$stmt1->bind_param("s", $studentIDVal);
	$stmt1->execute();
	$result1 = $stmt1->get_result();

    //First check if ID is correct
    if ($result1->num_rows === 0 ){
        echo json_encode(['status' => 'notMatch']);
        $stmt1->close();
        exit;
    }


	//Added short form of the tables along with join with user table to get the password
    $stmt2 = $conn->prepare("
        SELECT u.password, u.user_id, s.student_id, s.first_name, s.last_name, s.student_sex
        FROM student s 
        JOIN user u ON s.user_id = u.user_id 
        WHERE s.student_id = ?
    ");
    $stmt2->bind_param("s", $studentIDVal);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    //Then check if password is correct
    if ($result2->num_rows === 1) {
        $row = $result2->fetch_assoc();

            $hashedPassword = $row['password'];
            
            if (!password_verify($passwordVal, $hashedPassword)) {
                
                $stmt1->close();
                $stmt2->close();
                $conn->close();
                echo json_encode(['status' => 'wrongPass']);
                exit;
            }

            // Password correct, set session variables
            // For what I understand this could be used directly in PHP files because its in the
            // back end, so you don't need to keep using inputs.
            $_SESSION['userID'] = $row['user_id'];
            $_SESSION['studentID'] = $row['student_id'];
            $_SESSION['fullName'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['gender'] = $row['student_sex'];

            // Check if pre-test done
            $checkTest = $conn->prepare("SELECT 1 FROM fitness_test WHERE student_id = ? AND `test-type` = 'pre-test'");
            $checkTest->bind_param('s', $row['student_id']);
            $checkTest->execute();
            $checkResult = $checkTest->get_result();

            $preTestDone = ($checkResult->num_rows > 0) ? 1 : 0; //Ternary operation, checks if result is greater than zero, if yes, then one, if no, then zero

            // Send JSON response with success and preTestDone info
            // This is to make it easier to get data from the PHP file
            echo json_encode([
                'status' => 'success',
                'studentID' => $row['student_id'],
                'studentGender' => $row['student_sex'],
                'fullName' => $_SESSION['fullName'],
                'preTestDone' => $preTestDone,
                'userID' => $row['user_id']
            ]);
    } else{
        // No account with password
        echo json_encode(['status' => 'invalid']);
    }

    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>