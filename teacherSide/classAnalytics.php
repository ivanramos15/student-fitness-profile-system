<?php
session_start();
if (!isset($_SESSION['teacherID'])) {
    http_response_code(401);
    exit;
}

require_once '../database_conn.php';
//Get the section input then trim
$sectionInput = isset($_GET['section']) ? trim($_GET['section']) : '';
$parts = preg_split('/\s+/', $sectionInput); // split it
$section = '';
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

function runAvgQuery($conn, $sql, $params, $types) {
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
        AND ft.`test-type` = ?" . $filterClause;
    $result = runAvgQuery($conn, $sql, array_merge([$start_date, $end_date, $test_type], $filterParams), 'sss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_bmi = round($row['avg_bmi'] ?? 0, 2);
        $avg_pre_vo2 = round($row['avg_vo2'] ?? 0, 2);
    }
} else {
    $sql_pre = $avgSelect . "
        AND ft.`test-type` = 'pre-test'" . $filterClause;
    $sql_post = $avgSelect . "
        AND ft.`test-type` = 'post-test'" . $filterClause;

    $result = runAvgQuery($conn, $sql_pre, array_merge([$start_date, $end_date], $filterParams), 'ss' . $filterTypes);
    if ($result && $row = $result->fetch_assoc()) {
        $avg_pre_bmi = round($row['avg_bmi'] ?? 0, 2);
        $avg_pre_vo2 = round($row['avg_vo2'] ?? 0, 2);
    }

    $result = runAvgQuery($conn, $sql_post, array_merge([$start_date, $end_date], $filterParams), 'ss' . $filterTypes);
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
    AND ft.`date-taken` BETWEEN ? AND ?" . $filterClause;

$result = runAvgQuery($conn, $skillSelect, array_merge(['pre-test', $start_date, $end_date], $filterParams), 'sss' . $filterTypes);
if ($result && $row = $result->fetch_assoc()) {
    $avg_pre_scores = array_map('floatval', [
        $row['flexibility'],
        $row['strength'],
        $row['agility'],
        $row['speed'],
        $row['endurance']
    ]);
}

$result = runAvgQuery($conn, $skillSelect, array_merge(['post-test', $start_date, $end_date], $filterParams), 'sss' . $filterTypes);
if ($result && $row = $result->fetch_assoc()) {
    $avg_post_scores = array_map('floatval', [
        $row['flexibility'],
        $row['strength'],
        $row['agility'],
        $row['speed'],
        $row['endurance']
    ]);
}

$sql_count = "
    SELECT COUNT(DISTINCT f.student_id) AS total
    FROM fitness_test f
    JOIN student_section ss ON f.student_id = ss.student_id
    WHERE f.`date-taken` BETWEEN ? AND ?"
    . $filterClause
    . (!empty($test_type) ? " AND f.`test-type` = ?" : "");

$countParams = array_merge([$start_date, $end_date], $filterParams);
$countTypes = 'ss' . $filterTypes;
if (!empty($test_type)) {
    $countParams[] = $test_type;
    $countTypes .= 's';
}

$result = runAvgQuery($conn, $sql_count, $countParams, $countTypes);
$total_students = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_students = (int)$row['total'];
}

// Get latest post-test date
$sql_latest = "
    SELECT MAX(ft.`date-taken`) AS latest_date
    FROM fitness_test ft
    JOIN student_section ss ON ft.student_id = ss.student_id
    WHERE ft.`test-type` = 'post-test'" . $filterClause;

$result = runAvgQuery($conn, $sql_latest, $filterParams, $filterTypes);
$latest_date = '';
if ($result && $row = $result->fetch_assoc()) {
    $latest_date = $row['latest_date'] ?? '';
}

?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        background-color: #f5f7fa;
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.2em;
        color: black;
        font-weight: 300;
    }

    /* Filter Form */
    form#forms {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-end;
        gap: 15px;
        background: #ffffff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        max-width: 1200px;
        margin: 0 auto 40px auto;
    }

    form label {
        display: inline-block;
        font-size: 14px;
        margin-bottom: 5px;
        color: #555;
        font-weight: 500;
    }

    form input[type="text"],
    form input[type="date"],
    form select {
        width: 140px;
        height: 40px;
        margin-bottom: 5px;
        border-radius: 8px;
        border: 2px solid #e1e8ed;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    form input:focus,
    form select:focus {
        outline: none;
        border-color: #52c3a4;
        box-shadow: 0 0 0 3px rgba(82, 195, 164, 0.15);
    }

    .apply-btn {
        font-size: 14px;
        padding: 10px 25px;
        border-radius: 8px;
        color: white;
        border: none;
        background: linear-gradient(135deg, #52c3a4, #4a9d7e);
        cursor: pointer;
        height: 40px;
        font-weight: 500;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .apply-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(82, 195, 164, 0.3);
    }

    /* Main Container */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Top Row - Summary Cards */
    .summary-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    /* Summary Cards */
    .summary-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f4f7;
    }

    .summary-card h3 {
        color: #00796b;
        font-size: 1.3em;
        margin-bottom: 25px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .summary-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .summary-label {
        color: #6c757d;
        font-size: 15px;
        font-weight: 400;
    }

    .summary-value {
        color: #2c3e50;
        font-size: 16px;
        font-weight: 600;
    }

    /* Charts Row */
    .charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f4f7;
        display: flex;
        flex-direction: column;
    }

    .chart-card h4 {
        color: #00796b;
        font-size: 1.1em;
        margin-bottom: 20px;
        font-weight: 500;
        text-align: center;
    }

    .chart-container {
        flex: 1;
        position: relative;
        min-height: 250px;
    }

    .chart-container canvas {
        width: 100% !important;
        height: 100% !important;
    }

    /* Physical Ability Chart - Full Width */
    .physical-ability-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f4f7;
    }

    .physical-ability-card h4 {
        color: #00796b;
        font-size: 1.3em;
        margin-bottom: 25px;
        font-weight: 500;
        text-align: center;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .charts-row {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {

        .summary-row,
        .charts-row {
            grid-template-columns: 1fr;
        }

        form#forms {
            flex-direction: column;
            align-items: stretch;
        }

        form input[type="text"],
        form input[type="date"],
        form select {
            width: 100%;
        }
    }

    /* Chart Legends */
    .chart-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 15px;
        font-size: 12px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .pre-test {
        background-color: #52c3a4;
    }

    .post-test {
        background-color: #ff8c42;
    }
</style>

    <h2>Class Summary Analytics</h2>

    <form method="get" id="forms">
        <div>
            <label>Section:</label>
            <input type="text" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="Enter section">
        </div>
        <div>
            <label>Test Type:</label>
            <select name="test_type">
                <option value="">Both Tests</option>
                <option value="pre-test" <?php if ($test_type == 'pre-test') echo 'selected'; ?>>Pre-test</option>
                <option value="post-test" <?php if ($test_type == 'post-test') echo 'selected'; ?>>Post-test</option>
            </select>
        </div>
        <div>
            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>">
        </div>
        <div>
            <label>End Date:</label>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>">
        </div>
        <button type="submit" class="apply-btn">Apply Filters</button>
    </form>

    <div class="dashboard-container">
        <!-- Summary Row -->
        <div class="summary-row">
            <!-- Fitness Summary Card -->
            <div class="summary-card">
                <h3>📊 Fitness Summary</h3>
                <div class="summary-item">
                    <span class="summary-label">Current BMI</span>
                    <span class="summary-value" id="currentBmi"><?php
                                                                if (!empty($test_type)) {
                                                                    echo $avg_pre_bmi;
                                                                } else {
                                                                    echo $avg_post_bmi > 0 ? $avg_post_bmi : $avg_pre_bmi;
                                                                }
                                                                ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">VO₂ Max</span>
                    <span class="summary-value" id="currentVo2"><?php
                                                                if (!empty($test_type)) {
                                                                    echo $avg_pre_vo2 . ' ml/kg/min';
                                                                } else {
                                                                    echo ($avg_post_vo2 > 0 ? $avg_post_vo2 : $avg_pre_vo2) . ' ml/kg/min';
                                                                }
                                                                ?></span>
                </div>
            </div>


            <div class="summary-card">
                <h3>🕒 Recent Activity</h3>
                <div class="summary-item">
                    <span class="summary-label">Total Students</span>
                    <span class="summary-value" id="totalStudents"><?php echo $total_students; ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Last Post-Test</span>
                    <span class="summary-value"><?php echo $latest_date ? date('Y-m-d', strtotime($latest_date)) : 'N/A'; ?></span>
                </div>
            </div>
        </div>


        <div class="charts-row">

            <div class="chart-card">
                <h4>BMI Trend</h4>
                <div class="chart-container">
                    <canvas id="bmiChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color pre-test"></div>
                        <span>Pre: <?php echo $avg_pre_bmi; ?></span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color post-test"></div>
                        <span>Post: <?php echo $avg_post_bmi; ?></span>
                    </div>
                </div>
            </div>


            <div class="chart-card">
                <h4>VO₂ Max Progress</h4>
                <div class="chart-container">
                    <canvas id="vo2Chart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color pre-test"></div>
                        <span>Pre: <?php echo $avg_pre_vo2; ?></span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color post-test"></div>
                        <span>Post: <?php echo $avg_post_vo2; ?></span>
                    </div>
                </div>
            </div>


            <div class="chart-card">
                <h4>Class Overview</h4>
                <div style="padding: 20px; text-align: center;">
                    <div style="font-size: 3em; font-weight: bold; color: #00796b; margin-bottom: 10px;">
                        <?php echo $total_students; ?>
                    </div>
                    <div style="color: #6c757d; margin-bottom: 15px;">Total Students</div>
                    <div style="font-size: 0.9em; color: #6c757d;">
                        Section: <?php echo $section ? $section : 'All Sections'; ?>
                    </div>
                    <div style="font-size: 0.9em; color: #6c757d;">
                        Period: <?php echo date('M j', strtotime($start_date)) . ' - ' . date('M j, Y', strtotime($end_date)); ?>
                    </div>
                </div>
            </div>
        </div>


        <div class="physical-ability-card">
            <h4>Physical Ability</h4>
            <div class="chart-container" style="min-height: 400px;">
                <canvas id="radarChart"></canvas>
            </div>
            <div class="chart-legend" style="margin-top: 20px;">
                <div class="legend-item">
                    <div class="legend-color pre-test"></div>
                    <span>Pre-Test</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color post-test"></div>
                    <span>Post-Test</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let bmiChart, vo2Chart, radarChart;

            const initialData = {
                test_type: <?php echo json_encode($test_type); ?>,
                bmi: <?php echo !empty($test_type) ? json_encode($avg_pre_bmi) : json_encode([$avg_pre_bmi, $avg_post_bmi]); ?>,
                vo2: <?php echo !empty($test_type) ? json_encode($avg_pre_vo2) : json_encode([$avg_pre_vo2, $avg_post_vo2]); ?>,
                total_students: <?php echo $total_students; ?>,
                avg_pre_scores: <?php echo json_encode($avg_pre_scores); ?>,
                avg_post_scores: <?php echo json_encode($avg_post_scores); ?>
            };

            Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.color = '#6c757d';


            const bmiCtx = document.getElementById('bmiChart').getContext('2d');
            bmiChart = new Chart(bmiCtx, {
                type: 'bar',
                data: {
                    labels: initialData.test_type ? [initialData.test_type] : ['pre-test', 'post-test'],
                    datasets: [{
                        data: Array.isArray(initialData.bmi) ? initialData.bmi : [initialData.bmi],
                        backgroundColor: ['#52c3a4', '#8dd3c7'],
                        borderColor: '#52c3a4',
                        borderWidth: 0,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f8f9fa'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });


            const vo2Ctx = document.getElementById('vo2Chart').getContext('2d');
            vo2Chart = new Chart(vo2Ctx, {
                type: 'bar',
                data: {
                    labels: initialData.test_type ? [initialData.test_type] : ['pre-test', 'post-test'],
                    datasets: [{
                        data: Array.isArray(initialData.vo2) ? initialData.vo2 : [initialData.vo2],
                        backgroundColor: ['#52c3a4', '#8dd3c7'],
                        borderColor: '#52c3a4',
                        borderWidth: 0,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f8f9fa'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });


            const radarCtx = document.getElementById('radarChart').getContext('2d');
            radarChart = new Chart(radarCtx, {
                type: 'radar',
                data: {
                    labels: ['Flexibility', 'Strength', 'Agility', 'Speed', 'Endurance'],
                    datasets: initialData.test_type ? [{
                        label: initialData.test_type === 'pre-test' ? 'Pre-test Average' : 'Post-test Average',
                        data: initialData.test_type === 'pre-test' ? initialData.avg_pre_scores : initialData.avg_post_scores,
                        backgroundColor: initialData.test_type === 'pre-test' ? 'rgba(82, 195, 164, 0.2)' : 'rgba(255, 140, 66, 0.2)',
                        borderColor: initialData.test_type === 'pre-test' ? '#52c3a4' : '#ff8c42',
                        borderWidth: 2,
                        pointBackgroundColor: initialData.test_type === 'pre-test' ? '#52c3a4' : '#ff8c42',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }] : [{
                            label: 'Pre-test Average',
                            data: initialData.avg_pre_scores,
                            backgroundColor: 'rgba(82, 195, 164, 0.2)',
                            borderColor: '#52c3a4',
                            borderWidth: 2,
                            pointBackgroundColor: '#52c3a4',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Post-test Average',
                            data: initialData.avg_post_scores,
                            backgroundColor: 'rgba(255, 140, 66, 0.2)',
                            borderColor: '#ff8c42',
                            borderWidth: 2,
                            pointBackgroundColor: '#ff8c42',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 10
                                },
                                backdropColor: 'transparent'
                            },
                            grid: {
                                color: '#e9ecef'
                            },
                            angleLines: {
                                color: '#e9ecef'
                            },
                            pointLabels: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });


            document.getElementById('forms').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const params = new URLSearchParams(formData).toString();

                fetch('fetchAnalytics.php?' + params)
                    .then(response => response.json())
                    .then(data => {
                        const testType = formData.get('test_type');
                        updateCharts(data, testType);
                    })
                    .catch(error => console.error('Error:', error));
            });

            function updateCharts(data, test_type) {

                document.getElementById('totalStudents').textContent = data.total_students;

                if (!test_type) {
                    document.getElementById('currentBmi').textContent = data.bmi[1] || data.bmi[0];
                    document.getElementById('currentVo2').textContent = (data.vo2[1] || data.vo2[0]) + ' ml/kg/min';
                } else {
                    document.getElementById('currentBmi').textContent = data.bmi;
                    document.getElementById('currentVo2').textContent = data.vo2 + ' ml/kg/min';
                }


                const labels = !test_type ? ['pre-test', 'post-test'] : [test_type];

                bmiChart.data.labels = labels;
                bmiChart.data.datasets[0].data = Array.isArray(data.bmi) ? data.bmi : [data.bmi];
                bmiChart.update();

                vo2Chart.data.labels = labels;
                vo2Chart.data.datasets[0].data = Array.isArray(data.vo2) ? data.vo2 : [data.vo2];
                vo2Chart.update();


                if (test_type) {
                    radarChart.data.datasets = [{
                        label: test_type === 'pre-test' ? 'Pre-test Average' : 'Post-test Average',
                        data: data.avg_fitness_scores,
                        backgroundColor: test_type === 'pre-test' ? 'rgba(82, 195, 164, 0.2)' : 'rgba(255, 140, 66, 0.2)',
                        borderColor: test_type === 'pre-test' ? '#52c3a4' : '#ff8c42',
                        borderWidth: 2,
                        pointBackgroundColor: test_type === 'pre-test' ? '#52c3a4' : '#ff8c42',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }];
                } else {
                    radarChart.data.datasets = [{
                            label: 'Pre-test Average',
                            data: data.avg_pre_scores,
                            backgroundColor: 'rgba(82, 195, 164, 0.2)',
                            borderColor: '#52c3a4',
                            borderWidth: 2,
                            pointBackgroundColor: '#52c3a4',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Post-test Average',
                            data: data.avg_post_scores,
                            backgroundColor: 'rgba(255, 140, 66, 0.2)',
                            borderColor: '#ff8c42',
                            borderWidth: 2,
                            pointBackgroundColor: '#ff8c42',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ];
                }
                radarChart.update();
            }
        })();
    </script>