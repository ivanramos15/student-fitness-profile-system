<?php include_once "database_conn.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="js/jquery.js"></script>
  <link rel="stylesheet" href="css/general.css" />
  <link rel="stylesheet" href="css/signUp.css" />
  <title>Create Account</title>
</head>

<body>
  <div class="modal-container">
    <h1 class="signUp-title">SIGN UP</h1>
    <form action="signUpProcess.php" onsubmit="return signUp()" method="POST">
      <label for="firstName">First Name</label> <br />
      <input type="text" name="firstName" id="firstName" autocomplete="off" required />
      <br />

      <label for="lastName">Last Name</label> <br />
      <input type="text" name="lastName" id="lastName" autocomplete="off" required />
      <br />

      <label for="dateOfBirth">Date of Birth</label> <br/>
      <input type="date" id="dateOfBirth" name="dateOfBirth" max="<?php echo date('Y-m-d')?>" required>
      <br/>

      <label for="gender">Gender</label> <br />
      <select name="gender" id="gender">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select><br />

      <label for="section">Section</label> <br />
      <select class="add_select" name="section" required>
        <?php
        $result = $conn->query("SELECT section_id, program_id FROM section");
        while ($row = $result->fetch_assoc()) {
          echo "<option value='{$row['program_id']}/{$row['section_id']}'>{$row['program_id']} - {$row['section_id']}</option>";
        }
        ?>
      </select><br />

      <label for="password">Password</label> <br />
      <input
        type="password"
        name="password"
        id="password"
        autocomplete="off"
        class="pass-input"
        onkeyup="checkValidPassword();" required />
      <p class="pass-text pass"></p>

      <label for="conf-password">Confirm Password</label> <br />
      <input
        type="password"
        name="confPassword"
        id="conf-password"
        autocomplete="off"
        class="pass-input"
        onkeyup="checkConfPassword();" required />
      <p class="pass-text conf-pass"></p>

      <input type="submit" value="Sign Up" class="form-btn" /><br />
      <a href="login.html">Sign in</a>
    </form>
  </div>
  <script src="js/signUp.js"></script>

</body>

</html>