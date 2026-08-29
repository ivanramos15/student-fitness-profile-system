let passError = 0;
let confPassError = 0;
let oldPassError = 0;

let passWarning = document.querySelector(".pass");
let confWarning = document.querySelector(".conf-pass");

$(document).ready(function () {
  $(".main-content-div").on("click", "#change-password-button", function () {
    $(".modal-container").load(
      "updatePasswordModal.php",
      function (response, status, xhr) {
        if (status === "error") {
          console.error("Failed to load modal:", xhr.status, xhr.statusText);
          $(".modal-container").html(
            "<p>Error loading modal. Please try again later.</p>"
          );
        } else {
          $(".modal-container").fadeIn();
        }
      }
    );
  });

  $("body").on("click", ".modal-container", function (e) {
    if ($(e.target).is(".modal-container")) {
      $(".modal-container").fadeOut().empty();
    }
  });

  $(document).keydown(function (e) {
    if (e.key === "Escape") {
      $(".modal-container").fadeOut().empty();
    }
  });

  // Live check for old password
  $("body").on("keyup", "#old-password", function () {
    checkOldPassword();
  });

  // Live check for password strength
  $("body").on("keyup", "#new-password", function () {
    checkValidPassword();
  });

  // Live check for confirmation password match
  $("body").on("keyup", "#new-password-confirm", function () {
    checkConfPassword();
  });
  $("body").on("submit", "#change-password-form", function (e) {
    e.preventDefault(); // Prevent page reload
    changePassword();
  });
});

function checkOldPassword() {
  let oldPassVal = $("#old-password").val().trim();

  if (!oldPassVal) {
    $(".old-pass").text("Please enter password").css("color", "red");
    return;
  }

  $.ajax({
    url: "checkPassword.php",
    method: "POST",
    dataType: "json",
    data: { "old-password": oldPassVal },
    success: function (response) {
      if (response.status === "success") {
        $(".old-pass").text("Passwords match").css("color", "green");
        oldPassError = 0;
      } else if (response.status === "notMatch") {
        $(".old-pass").text("Password does not match").css("color", "red");
        oldPassError = 1;
      } else {
        $(".old-pass")
          .text("Error: " + (response.message || "Unknown error"))
          .css("color", "red");
        oldPassError = 1;
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error, xhr.responseText);
      $(".old-pass")
        .text("Server error (" + xhr.status + ")")
        .css("color", "red");
      oldPassError = 1;
    },
  });
}

function checkValidPassword() {
  const passwordPattern =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

  let password = $("#new-password").val();
  let passWarning = $(".pass"); // select the feedback <p> element fresh every call

  if (passwordPattern.test(password)) {
    passWarning.text("Password: Strong").css("color", "green");
    passError = 0;
    checkConfPassword(); // optionally call this to update confirmation message
  } else {
    passWarning.text("Password: Weak").css("color", "red");
    passError = 1;
  }
}

function checkConfPassword() {
  let password = $("#new-password").val();
  let confPassword = $("#new-password-confirm").val();
  let confWarning = $(".conf-pass"); // select feedback element

  if (password !== confPassword) {
    confWarning.text("Password not match!").css("color", "red");
    confPassError = 1;
  } else {
    confWarning.text("Password match!").css("color", "green");
    confPassError = 0;
  }
}

function checkNewPasswordDiffersFromOld() {
  let oldPass = $("#old-password").val();
  let newPass = $("#new-password").val();
  let passWarning = $(".pass");

  if (oldPass && newPass && oldPass === newPass) {
    passWarning
      .text("New password must be different from the old password")
      .css("color", "red");
    passError = 1; // mark error
    return false;
  } else {
    // Optionally clear the warning if valid
    passWarning.text("");
    return true;
  }
}

function changePassword() {
  checkValidPassword();
  checkConfPassword();

  if (!checkNewPasswordDiffersFromOld()) {
    showToast("New password must be different from the old password.", "error");
    return;
  }

  if (passError === 1) {
    showToast(
      "Password must be 8+ characters with uppercase, lowercase, number, and symbol.",
      "error"
    );
    return;
  }

  if (confPassError === 1) {
    showToast("Passwords do not match. Please try again.", "error");
    return;
  }

  // Check old password via AJAX
  $.ajax({
    url: "checkPassword.php",
    method: "POST",
    data: {
      "old-password": $("#old-password").val(),
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        // Proceed to update password
        $.ajax({
          url: "updatePasswordProcess.php",
          method: "POST",
          data: {
            "old-password": $("#old-password").val(),
            "new-password-confirm": $("#new-password-confirm").val(),
          },
          dataType: "json",
          success: function (updateResponse) {
            if (updateResponse.status === "success") {
              $userID = localStorage.getItem("userID");
              createRemark($userID, "changed-password");
              showToast("Successfully updated password.", "success");
              $(".modal-container").fadeOut().empty();
            } else {
              showToast("An error occurred. Password was not updated.", "error");
            }
          },
          error: function (xhr, status, error) {
            console.error(
              "Update AJAX Error:",
              status,
              error,
              xhr.responseText
            );
          },
        });
      } else if (response.status === "notMatch") {
        showToast("Old password is incorrect.", "error");
      } else {
        showToast("An error occurred while checking the old password.", "error");
      }
    },
    error: function (xhr, status, error) {
      console.error(
        "CheckPassword AJAX Error:",
        status,
        error,
        xhr.responseText
      );
    },
  });
}
