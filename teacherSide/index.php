<?php
session_start();
if (!isset($_SESSION['teacherID'])) {
  http_response_code(401);
  exit;
}
include_once "../database_conn.php";
$studentID = $_GET['studentID'] ?? '';
if ($studentID === '') {
    header("Location: teacherPage.php");
    exit;
}
$pre_strength = 0;
$pre_flexibility = 0;
$pre_endurance = 0;
$pre_speed = 0;
$pre_agility = 0;
$pre_bmi = 0;
$pre_vo2 = 0;
$pre_date = 'No Data';
$post_strength = 0;
$post_flexibility = 0;
$post_endurance = 0;
$post_speed = 0;
$post_agility = 0;
$post_bmi = 0;
$post_vo2 = 0;
$date = 'No Data';

$pretestsql = "SELECT * FROM fitness_test WHERE student_id = ? AND `test-type` = 'pre-test'";
$posttestsql = "SELECT * FROM `fitness_test` WHERE student_id = ? AND `test-type` = 'post-test' ORDER BY `test_id` DESC LIMIT 1";

$stmtPre = $conn->prepare($pretestsql);
$stmtPre->bind_param("s", $studentID);
$stmtPre->execute();
$pretest = $stmtPre->get_result();

if ($pretest->num_rows > 0) {
    $row = $pretest->fetch_assoc();
    $pre_bmi = $row['body_mass_index'];
    $pre_vo2 = $row['max_volume_of_oxygen'];
    $pre_date = $row['date-taken'];
    $pre_strength = $row['strength'];
    $pre_flexibility = $row['flexibility'];
    $pre_endurance = $row['endurance'];
    $pre_speed = $row['speed'];
    $pre_agility = $row['agility'];
} else {
}

$stmtPost = $conn->prepare($posttestsql);
$stmtPost->bind_param("s", $studentID);
$stmtPost->execute();
$posttest = $stmtPost->get_result();

if ($posttest->num_rows > 0) {
    $row = $posttest->fetch_assoc();
    $post_bmi = $row['body_mass_index'];
    $post_vo2 = $row['max_volume_of_oxygen'];
    $post_date = $row['date-taken'];
    $post_strength = $row['strength'];
    $post_flexibility = $row['flexibility'];
    $post_endurance = $row['endurance'];
    $post_speed = $row['speed'];
    $post_agility = $row['agility'];
    $date = $row['date-taken'];
}

$firstName = 'No Data';
$lastName = '';

$studentName = "SELECT * FROM student WHERE student_id = ?";
$stmtName = $conn->prepare($studentName);
$stmtName->bind_param("s", $studentID);
$stmtName->execute();
$resultName = $stmtName->get_result();
if ($resultName->num_rows > 0) {
    $rowName = $resultName->fetch_assoc();
    $firstName = $rowName['first_name'];
    $lastName =  $rowName['last_name'];
} else {
}

$fitnessData = [
    'post' => [
        'strength'    => $post_strength,
        'flexibility' => $post_flexibility,
        'endurance'   => $post_endurance,
        'speed'       => $post_speed,
        'agility'     => $post_agility,
        'bmi'         => $post_bmi,
        'vo2'         => $post_vo2,
    ],
    'pre' => [
        'strength'    => $pre_strength,
        'flexibility' => $pre_flexibility,
        'endurance'   => $pre_endurance,
        'speed'       => $pre_speed,
        'agility'     => $pre_agility,
        'bmi'         => $pre_bmi,
        'vo2'         => $pre_vo2,
    ],
];

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fitness Profile - Progress Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/teacherDashboard.css">
</head>
<header class="dashboard-header">
    <h1>Student Fitness Profile System</h1>
    <div class="user-info">
        <h1><?= "$firstName $lastName" ?> </h1>

    </div>
</header>
<main class="main-content">
    <button class="logout-btn" onclick="window.location.href='teacherPage.php'">Back</button>
<?php if ($pre_bmi === 0 && $pre_vo2 === 0): ?>
    <div class="empty-state">
        <h3>No test results yet</h3>
        <p>This student has not taken any fitness test.</p>
    </div>
<?php else: ?>
    <div class="cards-container">
        <div class="summary-card">
            <h3>Fitness Summary</h3>
            <div class="metric">
                <span class="metric-label">Current BMI:</span>
                <span class="metric-value">
                    <?php echo $post_bmi; ?>
                </span>
            </div>
            <div class="metric">
                <span class="metric-label">VO₂ Max:</span>
                <span class="metric-value"><?php echo $post_vo2; ?> ml/kg/min</span>
            </div>
        </div>

        <div class="summary-card">
            <h3>Recent Activity</h3>
            <div class="metric">
                <span class="metric-label">Last Post-Test:</span>
                <span class="metric-value"><?php echo $date; ?></span>
            </div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <h4>Physical Ability</h4>
            <div class="chart-wrapper">
                <canvas id="strengthChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h4>BMI Trend</h4>
            <div class="chart-wrapper">
                <canvas id="bmiChart"></canvas>
            </div>
            <div class="chart-footer">
                <span>Pre: <?= $pre_bmi ?></span>
                <span>Post: <?= $post_bmi ?></span>
            </div>
        </div>

        <div class="chart-card">
            <h4>VO₂ Max Progress</h4>
            <div class="chart-wrapper">
                <canvas id="vo2Chart"></canvas>
            </div>
            <div class="chart-footer">
                <span>Pre: <?= $pre_vo2 ?></span>
                <span>Post: <?= $post_vo2 ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
</main>

<div id="data"
    data-payload='<?php echo json_encode($fitnessData, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
</div>
<script src="../js/teacherCharts.js"></script>