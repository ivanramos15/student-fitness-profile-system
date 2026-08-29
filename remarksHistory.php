<?php
session_start();
if (!isset($_SESSION['userID'])) {
  http_response_code(401);
  exit;
}
include_once "database_conn.php";

$userID = $_SESSION['userID'];

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$start = ($page - 1) * $limit;

try {
    $countQuery = "SELECT COUNT(*) as total FROM activity_log WHERE user_id = ?";
    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $countStmt->bind_param("s", $userID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total_rows = $countResult->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    $query = "SELECT `time_done`, `remark` FROM `activity_log` WHERE user_id = ? ORDER BY time_done DESC LIMIT ?, ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sii", $userID, $start, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    // Render HTML
    echo "<div class='remarks-container'>";
    echo "<table id='remarksHistory'>";
    echo "<thead>
            <tr>
                <th>Time Done</th>
                <th>Remarks</th>
            </tr>
          </thead>
          <tbody>";

    $rowCount = 0;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            echo "<tr>
                    <td>{$row['time_done']}</td>
                    <td>{$row['remark']}</td>
                </tr>";
            $rowCount++;
        }
    } else {
        echo "<tr><td colspan='2'>No data found.</td></tr>";
        $rowCount = 1;
    }

    // Fill remaining rows to reach 10
    for ($i = $rowCount; $i < $limit; $i++) {
        echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
    }


    echo "</tbody></table>";

    $visible_pages = 5; // Number of page links to show around the current page
    $start_page = max(1, $page - floor($visible_pages / 2));
    $end_page = min($total_pages, $start_page + $visible_pages - 1);

    // Adjust start if end is near total_pages
    $start_page = max(1, $end_page - $visible_pages + 1);

    echo "<div id='remarks-pagination'>";

    // First/Prev
    if ($page > 1) {
        echo "<a href='#' class='remarks-page-link' data-page='1'>&laquo; First</a> ";
        echo "<a href='#' class='remarks-page-link' data-page='" . ($page - 1) . "'>&lsaquo; Prev</a> ";
    }

    // Left Ellipsis
    if ($start_page > 1) {
        echo "<span>...</span> ";
    }

    // Page Numbers
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? "style='font-weight:bold;'" : "";
        echo "<a href='#' class='remarks-page-link' data-page='$i' $active>$i</a> ";
    }

    // Right Ellipsis
    if ($end_page < $total_pages) {
        echo "<span>...</span> ";
    }

    // Next/Last
    if ($page < $total_pages) {
        echo "<a href='#' class='remarks-page-link' data-page='" . ($page + 1) . "'>Next &rsaquo;</a> ";
        echo "<a href='#' class='remarks-page-link' data-page='$total_pages'>Last &raquo;</a>";
    }

    echo "</div>";
    echo "</div>";


} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<div class='error'>An error occurred while fetching data.</div>";
} finally {
    $conn->close();
}
?>