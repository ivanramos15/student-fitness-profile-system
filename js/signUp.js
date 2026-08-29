"use strict";

// SIGN UP STUDENT

let passError = 0;
let confPassError = 0;

let passWarning = document.querySelector(".pass");
let confWarning = document.querySelector(".conf-pass");

function showToast(message, type) {
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = "toast " + (type || "");
  toast.textContent = message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("hide");
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

function checkValidPassword() {
  const passwordPattern =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

  let password = document.getElementById("password").value;

  if (passwordPattern.test(password)) {
    passWarning.textContent = "Password: Strong";
    passWarning.style.color = "green";
    passError = 0;
    checkConfPassword();
  } else {
    passWarning.textContent = "Password: Weak";
    passWarning.style.color = "red";
    passError = 1;
  }
}

function checkConfPassword() {
  const password = document.getElementById("password").value;
  const confPassword = document.getElementById("conf-password").value;
  if (password !== confPassword) {
    confWarning.textContent = "Password not match!";
    confWarning.style.color = "red";
    confPassError = 1;
  } else {
    confWarning.textContent = "Password match!";
    confWarning.style.color = "green";
    confPassError = 0;
  }
}

function signUp() {
  checkValidPassword();
  checkConfPassword();
  if (passError === 1) {
    showToast(
      "Password must be 8+ characters with uppercase, lowercase, number, and symbol.",
      "error"
    );
    return false;
  } else if (confPassError === 1) {
    showToast("Passwords do not match. Please try again.", "error");
    return false;
  } else {
    return true;
  }
}
