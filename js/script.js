"use strict";

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

$(document).ajaxStart(function () {
  let bar = document.getElementById("loading-bar");
  if (!bar) {
    bar = document.createElement("div");
    bar.id = "loading-bar";
    document.body.appendChild(bar);
  }
  bar.classList.remove("done");
  bar.classList.add("active");
});

$(document).ajaxStop(function () {
  const bar = document.getElementById("loading-bar");
  if (bar) {
    bar.classList.add("done");
    setTimeout(() => {
      bar.classList.remove("active", "done");
    }, 400);
  }
});

$(document).ajaxComplete(function (event, xhr, settings) {
  const content = document.querySelector(".main-content-div");
  if (content && settings.dataType !== "json") {
    content.classList.remove("fade-in");
    void content.offsetWidth;
    content.classList.add("fade-in");
  }
});

// for accessing different page in dashboard
// this is for checking if you're already in the page, if you are then you can't click it again.
let postTest,
  remarks,
  profile = 0,
  remarksHistory = 0;
let progress = 1;

// values use to determine which student is login
let studentID = "";
let studentGender = "";
let fullName = "";
let preTestDone = "";
let postTestDone = "";
let userID = "";

// LOGIN
// STUDENT LOGIN

function studentLogin() {
  $.ajax({
    url: "loginStudentProcess.php",
    method: "POST",
    data: {
      studentID: $(".studentID").val(),
      password: $(".password").val(),
    },
    dataType: "json",
  }).done((response) => {

    if (response.status === "success") {
      studentID = response.studentID;
      studentGender = response.studentGender;
      fullName = response.fullName;
      preTestDone = response.preTestDone;
      userID = response.userID;

      localStorage.setItem("studentID", response.studentID);
      localStorage.setItem("studentGender", response.studentGender);
      localStorage.setItem("fullName", response.fullName);
      localStorage.setItem("preTestDone", response.preTestDone);
      localStorage.setItem("userID", response.userID);

      createRemark(userID, "log in");
      showToast(`Welcome ${fullName}!`, "success");
      setTimeout(() => {
        window.location.href = "studentPage.php";
      }, 700);
    } else if (response.status === "notMatch") {
      showToast("Student ID has no match!", "error");
    } else if(response.status === "wrongPass"){
      showToast("Wrong password!", "error");
    }else if (response.status === "invalid") {
      showToast("Student ID and Password do not match!", "error");
    }
  });
  return false;
}

function loadStudentLogin() {
  $.ajax({
    url: "studentLogin.html",
  }).done((response) => {
    $(".login-inputs").html(response);
  });
}

// STUDENT LOGOUT
// This is for better handling of logging out and to easily make a remark
function logout() {
  $.ajax({
    type: "POST",
    url: "remarksCreationProcess.php",
    dataType: "json",
    data: { remark: "Logged Out" },
  }).always(() => {
    // After logging remark, redirect to logout.php to destroy session
    window.location.href = "logout.php";
  });

  return false;
}

// TEACHER LOGIN

function teacherLogin() {
  $.ajax({
    url: "loginTeacherProcess.php",
    method: "POST",
    data: {
      teacherID: $(".teacherID").val(),
      password: $(".password").val(),
    },
    dataType: "json",
  }).done((response) => {
    if (response.status === "success") {
      showToast("Welcome!", "success");
      setTimeout(() => {
        window.location.href = "teacherSide/teacherPage.php";
      }, 700);
    } else if (response.status === "notMatch") {
      showToast("Teacher ID and Password not match!", "error");
    }else if(response.status === "wrongPass"){
      showToast("Wrong password!");
    }else if (response.status === "invalid") {
      showToast("Teacher ID does not exist.", "error");
    }
  });

  return false;
}

$(document).ready(function () {
  $(document).on("click", ".student-btn", function () {
    $.ajax({
      url: "studentLogin.html",
    }).done((response) => {
      $(".login-inputs").html(response);
      $(".student-btn").css({
        backgroundColor: "#264653",
        color: "white",
      });

      $(".teacher-btn").css({
        backgroundColor: "white",
        color: "#264653",
      });
    });
  });

  $(document).on("click", ".teacher-btn", function () {
    $.ajax({
      url: "teacherLogin.html",
    }).done((response) => {
      $(".login-inputs").html(response);

      $(".teacher-btn").css({
        backgroundColor: "#264653",
        color: "white",
      });

      $(".student-btn").css({
        backgroundColor: "white",
        color: "#264653",
      });

      $(".login-btn").css({
        marginBottom: "0",
      });
    });
  });
});

// STUDENT PAGE

// DASHBOARD CLICK EVENTS

$(document).on("click", ".profile-link", function () {
  if (profile === 0) {
    $.ajax({
      url: `myProfile.php`,
    }).done((response) => {
      $(".main-content-div").html(response);
      $("nav ul li").css("backgroundColor", "#f5f5f5");
      $(".profile-link").css("backgroundColor", "#a2e3dc");
    });
    profile = 1;
    postTest = 0;
    remarks = 0;
    remarksHistory = 0;
    progress = 0;
  }
});

$(document).on("click", ".postTest-link", function () {
  if (postTest === 0) {
    loadPostTest();
  }
});

$(document).on("click", ".progress-link", function () {
  if (progress === 0) {
    loadProgress();
  }
});

//This function is for going to the Remarks History PHP
$(document).ready(function () {
  $(document).on("click", ".remarksHistory-link", function () {
    if (remarksHistory === 0) {
      const page = $(this).data("page") || 1;
      $.ajax({
        url: `remarksHistory.php?userID=${userID}&page=${page}`,
      }).done((response) => {
        $(".main-content-div").html(response);
        $("nav ul li").css("backgroundColor", "#f5f5f5");
        $(".remarksHistory-link").css("backgroundColor", "#a2e3dc");
      });
      remarksHistory = 1;
      profile = 0;
      postTest = 0;
      remarks = 0;
      progress = 0;
    }
  });
});

//This function is for loading the Remarks History based on pagination
$(document).ready(function () {
  $(document).on("click", ".remarks-page-link", function () {
    const page = $(this).data("page") || 1;
    $.ajax({
      url: `remarksHistory.php?userID=${userID}&page=${page}`,
    }).done((response) => {
      $(".main-content-div").html(response);
    });
  });
});

// FUNCTION LOADING DIFFERENT PAGES

// Function to check if may pre test or wala para ma direct sa page na dapat
function loadPage() {
  studentID = localStorage.getItem("studentID");
  studentGender = localStorage.getItem("studentGender");
  preTestDone = localStorage.getItem("preTestDone");
  fullName = localStorage.getItem("fullName");
  userID = localStorage.getItem("userID");

  const greetName = document.querySelector(".greet-name");

  greetName.textContent = "Welcome, " + fullName;

  if (preTestDone === "1") {
    loadProgress();
  } else {
    loadPreTest();
  }
}

function loadPreTest() {
  $.ajax({
    url: `preTestPage.php`,
  }).done((response) => {
    $(".main-content-div").html(response);

    // check if may script na ba na same file name and source para di madoble
    if (!document.querySelector('script[src="js/calculation.js"]')) {
      const script = document.createElement("script");
      script.src = "js/calculation.js";
      script.onload = () => {
        setUpCalcu();
      };
      document.body.appendChild(script);
    } else {
      setUpCalcu();
    }
    progress = 1;
    profile = 1;
    remarks = 1;
    postTest = 1;
    remarksHistory = 1;
  });
}

function loadPostTest() {
  $.ajax({
    url: `postTestPage.php`,
  }).done((response) => {
    $(".main-content-div").html(response);
    $("nav ul li").css("backgroundColor", "#f5f5f5");
    $(".postTest-link").css("backgroundColor", "#a2e3dc");

    if (!document.querySelector('script[src="js/calculation.js"]')) {
      const script = document.createElement("script");
      script.src = "js/calculation.js";
      script.onload = () => {
        setUpCalcu();
      };
      document.body.appendChild(script);
    } else {
      setUpCalcu();
    }
  });

  postTest = 1;
  profile = 0;
  remarks = 0;
  progress = 0;
  remarksHistory = 0;
}

function loadProgress() {
  $.ajax({
    url: `index.php`,
  }).done((response) => {
    $(".main-content-div").html(response);
    $("nav ul li").css("backgroundColor", "#f5f5f5");
    $(".progress-link").css("backgroundColor", "#a2e3dc");
    if (!document.querySelector('script[src="js/charts.js"]')) {
      const script = document.createElement("script");
      script.src = "js/charts.js";
      script.onload = () => {
        setUpChart();
      };
      document.body.appendChild(script);
    } else {
      setUpChart();
    }
  });
  progress = 1;
  profile = 0;
  remarks = 0;
  postTest = 0;
  remarksHistory = 0;
}

// PRE/POST TEST FUNCTIONS PROCESS
function processTest(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);
  $.ajax({
    url: `addTestProcess.php`,
    method: "POST",
    data: formData,
    dataType: "json",
    processData: false,
    contentType: false,
  }).done((response) => {
    if (response.status === "success") {
      $(".main-content-div").html(response.html);
      createRemark(userID, response.testType); //Creates remark based if its a post-test or pre-test
    } else {
      showToast("Error: " + response.message, "error");
    }
  });

  progress = 0;
  profile = 0;
  remarks = 0;
  postTest = 0;
  remarksHistory = 0;

  return false;
}

// UPDATE btn
$(document).on("click", ".update-btn", function () {
  loadPostTest();
});
