<?php
session_start();
if (!isset($_SESSION['studentID'])) {
  header("Location: login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="js/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/general.css" />
  <link rel="stylesheet" href="css/studentPage.css" />
  <link rel="stylesheet" href="css/stylesChart.css" />
  <title>Student Fitness Profile System</title>
</head>

<body onload="loadPage()">

  <div class="modal-container">
    <div class="modal-content-div">
      <form id="change-password-form" method="post">
        
        <h1 class="change-password-title">Change Password</h1>
        <label for="old-password">Old Password</label>
        <input type="password" name="old-password" id="old-password"
        onkeyup="checkOldPassword();"
        required>
        <p class="pass-text old-pass"></p>

        <label for="new-password">New Password</label>
        <input type="password" 
          name="new-password" 
          id="new-password"
          autocomplete="off"
          class="pass-input"
          onkeyup="checkValidPassword();" 
          required >
        <p class="pass-text pass"></p>

        <label for="new-password-confirm">Confirm New Password</label>
        <input type="password" 
          name="new-password-confirm" 
          id="new-password-confirm"
          autocomplete="off"
          class="pass-input"
          onkeyup="checkConfPassword();" 
          required />
        <p class="pass-text conf-pass"></p>
        <button type="submit" class="change-password-submit">Change Password</button>
        
      </form>
    </div>
  </div>

  <header>
    <h1>Student Fitness Profile System</h1>
    <div class="user-info">
      <span class="greet-name"></span>
      <form onSubmit="return logout();" method="post" style="display:inline;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </div>
  </header>

  <nav class="sidebar">
    <ul>
      <li class="progress-link active">
        <a href="#">My Progress</a>
      </li>
      <li class="postTest-link"><a href="#">Post-Test Form</a></li>
      <li class="remarksHistory-link"><a href="#">Remarks History</a></li>
      <li class="profile-link"><a href="#">My Profile</a></li>
    </ul>
  </nav>

  <div class="main-content-div"></div>

  <footer>
    <p>Bulacan State University © 2025</p>
  </footer>
  <script src="js/changePassword.js"></script>
  <script src="js/remarksCreation.js"></script>
  <script src="js/script.js"></script>
</body>

</html>