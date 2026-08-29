<?php
session_start();
if (!isset($_SESSION['studentID'])) {
  http_response_code(401);
  exit;
}
include_once "database_conn.php";

$studentID = $_SESSION['studentID'];

$height = $_POST['height'];
$weight = $_POST['bmi-weight'];
$bodyMassIndex = $_POST['bmi-result'];
$maxOxygen = $_POST['vo-result'];
$flexibility = $_POST['flexibility'];
$strength = $_POST['strength'];
$agility = $_POST['agility'];
$speed = $_POST['speed'];
$endurance = $_POST['endurance'];

$testType = '';





$checkTest = $conn->prepare("SELECT * FROM `student` s JOIN fitness_test f ON f.student_id = s.student_id where s.student_id = ? AND `test-type` = 'pre-test'");
$checkTest->bind_param('s', $studentID);
$checkTest->execute();
$checkResult = $checkTest->get_result();

if ($checkResult->num_rows > 0) {
	$testType = 'post-test';
} else {
	$testType = 'pre-test';
}


$sql = "INSERT INTO fitness_test 
(student_id, `test-type`, height, weight, body_mass_index, max_volume_of_oxygen, flexibility, strength, agility, speed, endurance, `date-taken`) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", 
  $studentID, 
  $testType, 
  $height, 
  $weight, 
  $bodyMassIndex, 
  $maxOxygen, 
  $flexibility, 
  $strength, 
  $agility, 
  $speed, 
  $endurance
);

if ($stmt->execute()) {
    $html = "
    <div class='background-div'>
      <div class='success-div'>
        <h3 class='success-title'>Test Submitted!</h3>
        <button onclick='loadProgress();' class='back-progress-btn'>See Progress</button>
      </div>
    </div>
    ";

    //Added json_encode so that I can know if it was successfull and get the test type
    echo json_encode([
      'status' => 'success',
      'testType' => $testType,
      'html' => $html
    ]);
} else {
    echo json_encode([
      'status' => 'error',
      'message' => $conn->error
    ]);
    
}