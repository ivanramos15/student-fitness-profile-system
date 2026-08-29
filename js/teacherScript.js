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

function loadManageStudents() {
  $.ajax({
    url: `manageStudents.php`,
  }).done((response) => {
    $(".main-content-div").html(response);
    $("nav ul li").css("backgroundColor", "#f5f5f5");
    $(".manageStudent-link").css("backgroundColor", "#a2e3dc");
    
    if (
      !document.querySelector('script[src="../js/manageStudents.js"]')
    ) {
      const script = document.createElement("script");
      script.src = "../js/manageStudents.js";
      script.onload = () => {
        setUp();
      };
      document.body.appendChild(script);
    } else {
      setUp();
    }
  });
}

$(document).ready(function () {
  $(document).on("click", ".manageStudent-link", function () {
    loadManageStudents();
  });
});

$(document).ready(function () {
  $(document).on("click", ".classAnalytics-link", function () {
    $.ajax({
      url: "classAnalytics.php",
    }).done((response) => {
      $(".main-content-div").html(response);
      $("nav ul li").css("backgroundColor", "#f5f5f5");
      $(".classAnalytics-link").css("backgroundColor", "#a2e3dc");
    });
  });
});

function applyFilter(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);
  $.ajax({
    url: `../teacherSide/fetchAnalytics.php`,
    method: "POST",
    data: formData,
    dataType: "json",
    processData: false,
    contentType: false,
  }).done((response) => {
  });

  return false;
}
