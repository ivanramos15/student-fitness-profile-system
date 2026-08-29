<?php
session_start();
if (!isset($_SESSION['teacherID'])) {
  http_response_code(401);
  exit;
}
require_once '../database_conn.php';

$sql = "
SELECT st.student_id, first_name, last_name, s.section_id, s.program_id FROM `student` st JOIN `student_section` s ON st.student_id = s.student_id;
";

if ($stmt = $conn->prepare($sql)) {
	$stmt->execute();
	$result = $stmt->get_result();
} else {
	die('Prepare failed: ' . $conn->error);
}
?>

<style>
		.dashboard-container {
			height: 720px;
		}

		.table-card {
			background-color: white;
			border-radius: 12px;
			padding: 1.5rem;
			box-shadow: 0 4px 16px rgba(0, 77, 64, 0.08);
			border: 1px solid #e0efed;
		}

		.table-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 1rem;
		}

		.search-input {
			padding: 0.5rem 1rem;
			border: 1px solid #cfe5e3;
			border-radius: 8px;
			width: 250px;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.search-input:focus {
			border-color: #009c94;
			outline: none;
			box-shadow: 0 0 0 3px rgba(0, 156, 148, 0.15);
		}

		table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			overflow: hidden;
			border-radius: 8px;
		}

		th,
		td {
			padding: 12px;
			text-align: center;
			font-size: 0.9rem;
		}

		thead th {
			background-color: #00796b;
			color: white;
			position: sticky;
			top: 0;
			z-index: 1;
		}

		tbody tr {
			background-color: #ffffff;
			transition: background-color 0.2s;
		}

		tbody tr:nth-child(even) {
			background-color: #f5f5f5;
		}

		tbody tr:hover {
			background-color: #e0f2f1;
			cursor: pointer;
		}

		.pagination {
			display: flex;
			justify-content: center;
			margin-top: 1.5rem;
			gap: 0.5rem;
		}

		.pagination button {
			padding: 0.5rem 0.75rem;
			border: none;
			background-color: #009c94;
			color: white;
			border-radius: 8px;
			cursor: pointer;
			transition: background-color 0.2s ease, transform 0.15s ease;
		}

		.pagination button:active {
			transform: scale(0.96);
		}

		.pagination button:disabled {
			background-color: #ccc;
			cursor: not-allowed;
		}

		.pagination button:hover:not(:disabled) {
			background-color: #00796b;
			cursor: pointer;
		}

		th {
			position: relative;
			cursor: pointer;
			user-select: none;
			transition: background-color 0.3s;
		}

		th:hover {
			background-color: #00695c;
		}

		th.sorted {
			background-color: #004d40 !important;
		}

		th::after {
			content: '';
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			font-size: 0.7rem;
		}

		th.sorted.asc::after {
			content: '▲';
		}

		th.sorted.desc::after {
			content: '▼';
		}
	</style>

	<div class="dashboard-container">

		<main class="main-content">
			<div class="table-card">
				<div class="table-header">
					<h2>Student Information</h2>
					<input
						type="text"
						class="search-input"
						id="searchBox"
						placeholder="Search by name or ID..."
						oninput="filterTable()" />
				</div>
				<table id="testsTable">
					<thead>
						<tr>
							<th onclick="sortTable('student_id')">Student ID</th>
							<th onclick="sortTable('first_name')">First Name</th>
							<th onclick="sortTable('last_name')">Last Name</th>
							<th onclick="sortTable('section_id')">Section</th>
							<th onclick="sortTable('program_id')">Program</th>
							<th onclick="sortTable('date')">Last Update</th>
						</tr>
					</thead>

					<tbody>
						<?php if ($result && $result->num_rows > 0): ?>
							<?php while ($row = $result->fetch_assoc()): ?>
								<?php
								$query = "SELECT `date-taken` FROM `fitness_test` 
              WHERE student_id = ?
              ORDER BY `date-taken` DESC
              LIMIT 1;";

								$stmt = $conn->prepare($query);
								$stmt->bind_param("s", $row['student_id']);
								$stmt->execute();
								$result2 = $stmt->get_result();
								$row2 = $result2->fetch_assoc();
								$dateTaken = $row2['date-taken'] ?? 'N/A';
								?>

								<tr
									style="cursor: pointer;"
									data-has-fitness="<?= $dateTaken !== 'N/A' ? '1' : '0' ?>"
									onclick="handleRowClick(this, '<?= urlencode($row['student_id']) ?>')">
									<td><?= htmlspecialchars($row['student_id']) ?></td>
									<td><?= htmlspecialchars($row['first_name']) ?></td>
									<td><?= htmlspecialchars($row['last_name']) ?></td>
									<td><?= htmlspecialchars($row['section_id']) ?></td>
									<td><?= htmlspecialchars($row['program_id']) ?></td>
									<td><?= htmlspecialchars($dateTaken) ?></td>
								</tr>
							<?php endwhile; ?>
						<?php else: ?>
							<tr>
								<td colspan="6">No fitness tests found.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<div class="pagination">
					<button onclick="prevPage()" id="prevBtn" disabled>Prev</button>
					<button onclick="nextPage()" id="nextBtn">Next</button>
				</div>
			</div>

		</main>


	</div>