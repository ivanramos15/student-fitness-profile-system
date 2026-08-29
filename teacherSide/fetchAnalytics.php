<?php
session_start();
if (!isset($_SESSION['teacherID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
header('Content-Type: application/json');

require_once '../database_conn.php';
//Get the section input then trim
$sectionInput = isset($_GET['section']) ? trim($_GET['section']) : '';
$parts = preg_split('/\s+/', $sectionInput); // split it
$filterParts = [];
$filterParams = [];
$filterTypes = '';

if (count($parts) === 2) {
    $section = trim($parts[0]);
    $program = trim($parts[1]);
    if ($section !== '') {
        $filterParts[] = "ss.section_id = ?";
        $filterParams[] = $section;
        $filterTypes .= 's';
    }
    if ($program !== '') {
        $filterParts[] = "ss.program_id = ?";
        $filterParams[] = $program;
        $filterTypes .= 's';
    }
} else if (count($parts) === 1 && trim($parts[0]) !== '') { //if there is only one, use it both filters
    $filterParts[] = "(ss.section_id = ? OR ss.program_id = ?)";
    $filterParams[] = trim($parts[0]);
    $filterParams[] = trim($parts[0]);
    $filterTypes .= 'ss';
}

$filterClause = $filterParts ? " AND " . implode(" AND ", $filterParts) : '';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '2025-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '2025-12-31';
$test_type = isset($_GET['test_type']) ? $_GET['test_type'] : '';

function runQuery($conn, $sql, $params, $types) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

$avg_pre_bmi = $avg_post_bmi = 0;
$avg_pre_vo2 = $avg_post_vo2 = 0;

$avgSelect = "
        SELECT AVG(ft.body_mass_index) AS avg_bmi,
               AVG(ft.max_volume_of_oxygen) AS avg_vo2
        FROM fitness_test ft
        JOIN student_section ss ON ft.student_id = ss.student_id
        WHERE ft.`date-taken` BETWEEN ? AND ?";

if (!empty($test_type)) {

    $sql = $avgSelect . "
        AND ft.`test-type` = ?
        " . $filterClause;

    $result = runQuery($conn, $sql, array_merge([$start_date, $end_date, $test_type], $filterParams), 'sss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_bmi = round($row['avg_bmi'] ?? 0, 2);
        $avg_pre_vo2 = round($row['avg_vo2'] ?? 0, 2);
    }
} else {

    $sql_pre = $avgSelect . "
        AND ft.`test-type` = 'pre-test'
        " . $filterClause;

    $sql_post = $avgSelect . "
        AND ft.`test-type` = 'post-test'
        " . $filterClause;

    $result = runQuery($conn, $sql_pre, array_merge([$start_date, $end_date], $filterParams), 'ss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_bmi = round($row['avg_bmi'] ?? 0, 2);
        $avg_pre_vo2 = round($row['avg_vo2'] ?? 0, 2);
    }

    $result = runQuery($conn, $sql_post, array_merge([$start_date, $end_date], $filterParams), 'ss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_post_bmi = round($row['avg_bmi'] ?? 0, 2);
        $avg_post_vo2 = round($row['avg_vo2'] ?? 0, 2);
    }
}

$avg_pre_scores = [0, 0, 0, 0, 0];
$avg_post_scores = [0, 0, 0, 0, 0];

$skillSelect = "
        SELECT AVG(flexibility) AS flexibility,
               AVG(strength) AS strength,
               AVG(agility) AS agility,
               AVG(speed) AS speed,
               AVG(endurance) AS endurance
        FROM fitness_test ft
        JOIN student_section ss ON ft.student_id = ss.student_id
        WHERE ft.`test-type` = ?
        AND ft.`date-taken` BETWEEN ? AND ?
        " . $filterClause;

if (!empty($test_type)) {

    $result = runQuery($conn, $skillSelect, array_merge([$test_type, $start_date, $end_date], $filterParams), 'sss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_scores = array_map('floatval', [
            $row['flexibility'] ?? 0,
            $row['strength'] ?? 0,
            $row['agility'] ?? 0,
            $row['speed'] ?? 0,
            $row['endurance'] ?? 0
        ]);
    }
} else {

    $result = runQuery($conn, $skillSelect, array_merge(['pre-test', $start_date, $end_date], $filterParams), 'sss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_scores = array_map('floatval', [
            $row['flexibility'] ?? 0,
            $row['strength'] ?? 0,
            $row['agility'] ?? 0,
            $row['speed'] ?? 0,
            $row['endurance'] ?? 0
        ]);
    }

    $result = runQuery($conn, $skillSelect, array_merge(['post-test', $start_date, $end_date], $filterParams), 'sss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_post_scores = array_map('floatval', [
            $row['flexibility'] ?? 0,
            $row['strength'] ?? 0,
            $row['agility'] ?? 0,
            $row['speed'] ?? 0,
            $row['endurance'] ?? 0
        ]);
    }
}


$sql = "
SELECT f.flexibility, COUNT(*) AS count
FROM fitness_test f
JOIN student_section ss ON f.student_id = ss.student_id
WHERE f.`date-taken` BETWEEN ? AND ?
" . $filterClause
. (!empty($test_type) ? " AND f.`test-type` = ?" : "") . "
GROUP BY f.flexibility
ORDER BY f.flexibility;
";

$distParams = array_merge([$start_date, $end_date], $filterParams);
$distTypes = 'ss' . $filterTypes;
if (!empty($test_type)) {
    $distParams[] = $test_type;
    $distTypes .= 's';
}

$result = runQuery($conn, $sql, $distParams, $distTypes);
$flexibility_labels = [];
$flexibility_data = [];
$total_flex_score = 0;
$total_flex_count = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $score = (int)$row['flexibility'];
        $count = (int)$row['count'];
        $flexibility_labels[] = 'Score ' . $score;
        $flexibility_data[] = $count;
        $total_flex_score += $score * $count;
        $total_flex_count += $count;
    }
}

$avg_flexibility = $total_flex_count > 0 ? round($total_flex_score / $total_flex_count, 2) : 0;


$sql_count = "
    SELECT COUNT(DISTINCT f.student_id) AS total
    FROM fitness_test f
    JOIN student_section ss ON f.student_id = ss.student_id
    WHERE f.`date-taken` BETWEEN ? AND ?
    " . $filterClause
    . (!empty($test_type) ? " AND f.`test-type` = ?" : "");

$result = runQuery($conn, $sql_count, $distParams, $distTypes);
$total_students = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_students = (int)$row['total'];
}


if ($test_type == 'pre-test' || $test_type == 'post-test') {
    echo json_encode([
        'bmi' => $avg_pre_bmi,
        'vo2' => $avg_pre_vo2,
        'flex_labels' => $flexibility_labels,
        'flex_data' => $flexibility_data,
        'avg_flex' => $avg_flexibility,
        'total_students' => $total_students,
        'avg_fitness_scores' => $avg_pre_scores,
        'test_type' => $test_type
    ]);
} else {
    echo json_encode([
        'bmi' => [$avg_pre_bmi, $avg_post_bmi],
        'vo2' => [$avg_pre_vo2, $avg_post_vo2],
        'flex_labels' => $flexibility_labels,
        'flex_data' => $flexibility_data,
        'avg_flex' => $avg_flexibility,
        'total_students' => $total_students,
        'avg_pre_scores' => $avg_pre_scores,
        'avg_post_scores' => $avg_post_scores,
        'test_type' => null
    ]);
}
?>
