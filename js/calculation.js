function setUpCalcu() {
  const heightInput = document.getElementById("height");
  const bmiWeightInput = document.getElementById("bmi-weight");
  const bmiResult = document.getElementById("bmi-result");
  const bmiCategory = document.getElementById("bmi-category");

  const voWeightInput = document.getElementById("vo2-weight");
  const voAgeInput = document.getElementById("vo2-age");
  const voGenderInput = document.getElementById("gender");
  const voTimeInput = document.getElementById("time");
  const voHeartRateInput = document.getElementById("heartRate");
  const voResult = document.getElementById("vo-result");
  const voCategory = document.getElementById("vo-category");

  function calculateBMI() {
    const height = parseFloat(heightInput.value);
    const weight = parseFloat(bmiWeightInput.value);

    if (!height || !weight || height <= 0 || weight <= 0) {
      bmiResult.value = "";
      bmiCategory.textContent = "";
      return;
    }

    const bmi = weight / (height * height);
    bmiResult.value = bmi.toFixed(2);

    let category = "";
    if (bmi < 18.5) {
      category = "Underweight";
      bmiCategory.style.border = "2px solid #03a9f4";
    } else if (bmi < 24.9) {
      category = "Normal weight";
      bmiCategory.style.border = "2px solid #4caf50";
    } else if (bmi < 29.9) {
      category = "Overweight";
      bmiCategory.style.border = "2px solid #ff9800";
    } else {
      category = "Obese";
      bmiCategory.style.border = "2px solid #f44336";
    }

    bmiCategory.textContent = `BMI Category: ${category}`;
  }

  function calculateVO2Max() {
    const weight = parseFloat(voWeightInput.value);
    const age = parseInt(voAgeInput.value);
    let gender;

    if (voGenderInput.value === "Male") gender = 1;
    else gender = 0;

    const time = parseFloat(voTimeInput.value);
    const heartRate = parseInt(voHeartRateInput.value);

    if (
      !weight ||
      weight <= 0 ||
      !age ||
      age <= 0 ||
      !time ||
      time <= 0 ||
      !heartRate ||
      heartRate <= 0
    ) {
      voResult.value = "";
      voCategory.textContent = "";
      return;
    }

    const vo2Max =
      132.853 -
      0.0769 * weight -
      0.3877 * age +
      6.315 * gender -
      3.2649 * time -
      0.1565 * heartRate;

    // voResult.textContent = `Your estimated VO2 Max is: ${vo2Max.toFixed(2)} ml/kg/min`;
    voResult.value = vo2Max.toFixed(2);

    let category = "";
    if (vo2Max < 25) {
      category = "Very Poor";
      voCategory.style.border = "2px solid #e53935";
    } else if (vo2Max < 35) {
      category = "Poor";
      voCategory.style.border = "2px solid #fb8c00";
    } else if (vo2Max < 45) {
      category = "Fair";
      voCategory.style.border = "2px solid #fdd835";
    } else if (vo2Max < 55) {
      category = "Good";
      voCategory.style.border = "2px solid #43a047";
    } else {
      category = "Excellent";
      voCategory.style.border = "2px solid #1e88e5";
    }

    voCategory.textContent = `Fitness Category: ${category}`;
  }

  [heightInput, bmiWeightInput].forEach((input) =>
    input.addEventListener("input", calculateBMI)
  );

  [voWeightInput, voAgeInput, voTimeInput, voHeartRateInput].forEach((input) =>
    input.addEventListener("input", calculateVO2Max)
  );

  voGenderInput.addEventListener("change", calculateVO2Max);

}
