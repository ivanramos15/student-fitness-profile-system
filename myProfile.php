<?php
session_start();
if (!isset($_SESSION['studentID'])) {
  http_response_code(401);
  exit;
}
include_once "database_conn.php";

$studentID = $_SESSION['studentID'];

$stmt1 = $conn->prepare("SELECT 
    s.student_id, 
    first_name, 
    last_name, 
    student_sex, 
    DATE_FORMAT(date_of_birth, '%M %e, %Y') AS date_of_birth, 
    program_id, 
    section_id 
FROM `student` s
JOIN student_section sc ON s.student_id = sc.student_id
WHERE s.student_id = ?");
$stmt1->bind_param("s", $studentID);
$stmt1->execute();
$result1 = $stmt1->get_result();
$studentValues = $result1->fetch_assoc();

$stmt2 = $conn->prepare("SELECT * FROM `fitness_test` WHERE student_id = ? ORDER BY `test_id` DESC LIMIT 1");
$stmt2->bind_param("s", $studentID);
$stmt2->execute();
$result2 = $stmt2->get_result();
$fitnessValues = $result2->fetch_assoc() ?: [
  'height' => 'No data yet',
  'weight' => 'No data yet',
  'body_mass_index' => 'No data yet',
  'max_volume_of_oxygen' => 'No data yet',
  'flexibility' => null,
  'strength' => null,
  'agility' => null,
  'speed' => null,
  'endurance' => null,
  'date-taken' => 'No data yet',
];

function getFitnessRating($ratingNumber)
{
  switch ($ratingNumber) {
    case 1:
      return "Poor";
    case 2:
      return "Fair";
    case 3:
      return "Satisfactory";
    case 4:
      return "Excellent";
    default:
      return "Unknown";
  }
}

?>

<div class="profile-container">
  <h2 class="profile-title">My Profile</h2>
  <p class="p-message">
    Review and edit your fitness profile to keep it up to date.
  </p>
  <div class="profile-main-container">
    <div class="profile-div profile-box">
      <h3 class="fitness-profile-title">Personal Details</h3>
      <div class="profile-details">
        <p class="details-title">Student ID</p>
        <p class="details-value"><?php echo $studentValues['student_id']; ?></p>
      </div>
      <div class="profile-details">
        <p class="details-title">First Name</p>
        <p class="details-value"><?php echo $studentValues['first_name']; ?></p>
      </div>
      <div class="profile-details">
        <p class="details-title">Last Name</p>
        <p class="details-value"><?php echo $studentValues['last_name']; ?></p>
      </div>
      <div class="profile-details">
        <p class="details-title">Section</p>
        <p class="details-value"><?php echo $studentValues['program_id'] . ' ' . $studentValues['section_id']; ?></p>
      </div>
      <div class="profile-details">
        <p class="details-title">Gender</p>
        <p class="details-value"><?php echo $studentValues['student_sex']; ?></p>
      </div>
      <div class="dateOfBirth-btn-div">
        <div class="profile-details date_of_birth-div">
          <p class="details-title">Date of birth</p>
          <p class="details-value"><?php echo $studentValues['date_of_birth']; ?></p>
        </div>
      </div>
      <div class="profile-details change-btn">
        <button class="btn" id="change-password-button">Change Password</button>
      </div>
    </div>

    <div class="fitness-profile-div profile-box">
      <h3 class="fitness-profile-title">Fitness Profile</h3>
      <div class="fitness-details">
        <p class="details-title">Height (m)</p>
        <p class="details-value"><?php echo $fitnessValues['height']; ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Weight (kg)</p>
        <p class="details-value"><?php echo $fitnessValues['weight']; ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Body Mass Index (BMI)</p>
        <p class="details-value"><?php echo $fitnessValues['body_mass_index']; ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Maximum Volume of Oxygen (VO2)</p>
        <p class="details-value"><?php echo $fitnessValues['max_volume_of_oxygen']; ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Flexibility</p>
        <p class="details-value"><?php echo getFitnessRating($fitnessValues['flexibility']); ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Strength</p>
        <p class="details-value"><?php echo getFitnessRating($fitnessValues['strength']); ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Agility</p>
        <p class="details-value"><?php echo getFitnessRating($fitnessValues['agility']); ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Speed</p>
        <p class="details-value"><?php echo getFitnessRating($fitnessValues['speed']); ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Endurance</p>
        <p class="details-value"><?php echo getFitnessRating($fitnessValues['endurance']); ?></p>
      </div>
      <div class="fitness-details">
        <p class="details-title">Date Taken</p>
        <p class="details-value"><?php echo $fitnessValues['date-taken']; ?></p>
      </div>
      <button class="btn update-btn">Update</button>
    </div>
  </div>
</div>
<div class="modal"></div>
<div class="overlay"></div>