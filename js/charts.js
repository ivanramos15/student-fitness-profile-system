function setUpChart() {
  const el = document.getElementById("data");
  if (!el || !document.getElementById("bmiChart")) return;
  const data = JSON.parse(el.dataset.payload);

  const {
    post: {
      strength: post_strengthValue,
      flexibility: post_flexibilityValue,
      endurance: post_enduranceValue,
      speed: post_speedValue,
      agility: post_agilityValue,
      bmi: post_bmiValue,
      vo2: post_vo2Value,
    },
    pre: {
      strength: pre_strengthValue,
      flexibility: pre_flexibilityValue,
      endurance: pre_enduranceValue,
      speed: pre_speedValue,
      agility: pre_agilityValue,
      bmi: pre_bmiValue,
      vo2: pre_vo2Value,
    },
  } = data;

  const bmiCtx = document.getElementById("bmiChart").getContext("2d");
  const bmiChart = new Chart(bmiCtx, {
    type: "bar",
    data: {
      labels: ["pre-test", "post-test"],
      datasets: [
        {
          label: "BMI",
          data: [pre_bmiValue, post_bmiValue],
          backgroundColor: [
            "rgba(0, 156, 148, 0.7)",
            "rgba(77, 182, 172, 0.7)",
          ],
          borderColor: ["rgba(0, 156, 148, 1)", "rgba(77, 182, 172, 1)"],
          borderWidth: 1,
          tension: 0.3,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
    },
  });

  const vo2Ctx = document.getElementById("vo2Chart").getContext("2d");
  const vo2Chart = new Chart(vo2Ctx, {
    type: "bar",
    data: {
      labels: ["Pre-Test", "Post-Test"],
      datasets: [
        {
          label: "VO₂ Max (ml/kg/min)",
          data: [pre_vo2Value, post_vo2Value],
          backgroundColor: [
            "rgba(0, 156, 148, 0.7)",
            "rgba(77, 182, 172, 0.7)",
          ],
          borderColor: ["rgba(0, 156, 148, 1)", "rgba(77, 182, 172, 1)"],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });

  const strengthCtx = document.getElementById("strengthChart").getContext("2d");
  const strengthChart = new Chart(strengthCtx, {
    type: "radar",
    data: {
      labels: ["Flexibility", "Endurance", "Strength", "Speed", "Agility"],
      datasets: [
        {
          label: "Pre-Test",
          data: [
            pre_flexibilityValue,
            pre_enduranceValue,
            pre_strengthValue,
            pre_speedValue,
            pre_agilityValue,
          ],
          backgroundColor: "rgba(0, 156, 148, 0.2)",
          borderColor: "#00796B",
          pointBackgroundColor: "#00796B",
          pointBorderColor: "#fff",
          pointHoverBackgroundColor: "#fff",
          pointHoverBorderColor: "#00796B",
          borderWidth: 2,
        },
        {
          label: "Post-Test",
          data: [
            post_flexibilityValue,
            post_enduranceValue,
            post_strengthValue,
            post_speedValue,
            post_agilityValue,
          ],
          backgroundColor: "rgba(255, 152, 0, 0.2)",
          borderColor: "#FF6D00",
          pointBackgroundColor: "#FF6D00",
          pointBorderColor: "#fff",
          pointHoverBackgroundColor: "#fff",
          pointHoverBorderColor: "#FF6D00",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            usePointStyle: true,
            padding: 20,
          },
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return context.dataset.label + ": " + context.raw;
            },
          },
        },
      },
      scales: {
        r: {
          angleLines: {
            display: true,
            color: "rgba(0, 0, 0, 0.1)",
          },
          suggestedMin: 0,
          suggestedMax: 4,
        },
      },
    },
  });
}
