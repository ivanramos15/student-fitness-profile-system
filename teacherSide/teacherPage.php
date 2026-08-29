<?php
session_start();
if (!isset($_SESSION['teacherID'])) {
  header("Location: ../login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="../js/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../css/general.css" />
  <link rel="stylesheet" href="../css/teacherPage.css" />
  <title>Student Fitness Profile System</title>

</head>

<body onload="loadManageStudents()">
  <header>
    <h1>Student Fitness Profile System</h1>
    <form action="../logout.php" method="post" style="display:inline;">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </header>

  <nav class="sidebar">
    <ul>
      <li class="manageStudent-link active">
        <a href="#">Manage Students</a>
      </li>
      <li class="classAnalytics-link"><a href="#">Class Analytics Overview</a></li>
    </ul>
  </nav>

  <div class="main-content-div">
  </div>

  <footer>
    <p>Bulacan State University © 2025</p>
  </footer>
  <script src="../js/manageStudents.js"></script>
  <script src="../js/teacherScript.js"></script>
</body>

</html>