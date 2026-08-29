<?php
session_start();
if (!isset($_SESSION['studentID'])) {
  http_response_code(401);
  exit;
}
include_once "database_conn.php";
include_once "calculateAge.php";

$studentID = $_SESSION['studentID'];

$stmt = $conn->prepare("SELECT * FROM `student` s
JOIN fitness_test f
ON s.student_id = f.student_id
WHERE s.student_id = ?
ORDER BY test_id DESC
LIMIT 1;");
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc() ?: [
  'height' => '',
  'weight' => '',
  'body_mass_index' => '',
  'max_volume_of_oxygen' => '',
  'student_sex' => '',
  'date_of_birth' => '',
  'flexibility' => null,
  'strength' => null,
  'agility' => null,
  'speed' => null,
  'endurance' => null,
];

?>
<h2>Update your profile!</h2>
<p class="p-message">Fill out the forms to update your fitness profile and see your progress.</p>
<div class="test-form-container">
	<h3 class="title-form">Fitness Profile</h3>
	<div class="test-form">
		<h2 class="section-title">BMI Calculator</h2>
		<form method="POST" onsubmit="return processTest(event);">

			<div class="section-container">
				<label for="height">Enter your height (in meters):</label><br />
				<input
					type="number"
					id="height"
					step="0.01"
					name="height"
					value="<?php echo $row['height']; ?>"
					placeholder="Height in meters"
					required />
				<br />

				<label for="bmi-weight">Enter your weight (in kilograms):</label><br />
				<input
					type="number"
					id="bmi-weight"
					name="bmi-weight"
					step="any"
					value="<?php echo $row['weight']; ?>"
					placeholder="Weight in kilograms"
					required />
				<br />

				<div id="" class="bmi-result">
					<label for="bmi-result">BMI Result:</label><br />
					<input
						type="number"
						id="bmi-result"
						name="bmi-result"
						value="<?php echo $row['body_mass_index']; ?>"
						readonly />
					<br />
				</div>
				<p id="bmi-category" class="bmi-category"></p>
			</div>
			<div class="section-container">
				<h2 class="section-title">Vo2 Calculator</h2>

				<label for="vo2-weight">Weight (in pounds):</label> <br />
				<input
					type="number"
					id="vo2-weight"
					name="vo2-weight"
					placeholder=""
					readonly
					required /><br />

				<label for="vo2-age">Age (in years):</label><br />
				<input
					type="number"
					id="vo2-age"
					name="age"
					value="<?php echo calculateAge($row['date_of_birth']); ?>"
					placeholder="Age in years"
					readonly
					required /><br />

				<label for="gender">Gender:</label> <br />
				<select id="gender" name="gender">
					<option value="<?php echo $row['student_sex']; ?>"><?php echo $row['student_sex']; ?></option>
				</select><br />

				<label for="time">Enter the time taken to walk 1 mile (in minutes):</label>
				<br />
				<input type="number" id="time" placeholder="Time in minutes" required />
				<br />

				<label for="heartRate">Enter your heart rate after finishing the walk:</label><br />
				<input
					type="number"
					id="heartRate"
					name="heartRate"
					placeholder="Heart rate in bpm"
					required /><br />

				<div class="result-container">
					<label for="">Estimated VO2 Max (ml/kg/min):</label><br />
					<input
						type="number"
						id="vo-result"
						name="vo-result"
						value="<?php echo $row['max_volume_of_oxygen']; ?>"

						readonly />
				</div>
				<p id="vo-category" class="category-container"></p>
			</div>
			<div class="section-container">
				<h2 class="section-title">Skill-Based Fitness Assessment</h2>
				<div class="skill-choices-container">
					<label class="skill-title">Flexibility:</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="4" <?php if (isset($row['flexibility']) && $row['flexibility'] == 4) echo 'checked'; ?> required /> Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="3" <?php if (isset($row['flexibility']) && $row['flexibility'] == 3) echo 'checked'; ?> /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="2" <?php if (isset($row['flexibility']) && $row['flexibility'] == 2) echo 'checked'; ?> /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="1" <?php if (isset($row['flexibility']) && $row['flexibility'] == 1) echo 'checked'; ?> /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Strength:</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="4" <?php if (isset($row['strength']) && $row['strength'] == 4) echo 'checked'; ?> required /> Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="3" <?php if (isset($row['strength']) && $row['strength'] == 3) echo 'checked'; ?> /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="2" <?php if (isset($row['strength']) && $row['strength'] == 2) echo 'checked'; ?> /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="1" <?php if (isset($row['strength']) && $row['strength'] == 1) echo 'checked'; ?> /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Agility:</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="4" <?php if (isset($row['agility']) && $row['agility'] == 4) echo 'checked'; ?> required /> Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="3" <?php if (isset($row['agility']) && $row['agility'] == 3) echo 'checked'; ?> /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="2" <?php if (isset($row['agility']) && $row['agility'] == 2) echo 'checked'; ?> /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="1" <?php if (isset($row['agility']) && $row['agility'] == 1) echo 'checked'; ?> /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Speed:</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="4" <?php if (isset($row['speed']) && $row['speed'] == 4) echo 'checked'; ?> required /> Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="3" <?php if (isset($row['speed']) && $row['speed'] == 3) echo 'checked'; ?> /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="2" <?php if (isset($row['speed']) && $row['speed'] == 2) echo 'checked'; ?> /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="1" <?php if (isset($row['speed']) && $row['speed'] == 1) echo 'checked'; ?> /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Endurance:</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="4" <?php if (isset($row['endurance']) && $row['endurance'] == 4) echo 'checked'; ?> required /> Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="3" <?php if (isset($row['endurance']) && $row['endurance'] == 3) echo 'checked'; ?> /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="2" <?php if (isset($row['endurance']) && $row['endurance'] == 2) echo 'checked'; ?> /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="1" <?php if (isset($row['endurance']) && $row['endurance'] == 1) echo 'checked'; ?> /> Poor</label><br />
				</div>

			</div>
			<input type="submit" class="btn submit-btn" value="Submit">
		</form>
	</div>
</div>

<script>
	function setValues() {
		const weightPoundsInput = document.getElementById("vo2-weight");
		const inputBmiWeight = document.getElementById('bmi-weight');
		const weightKg = document.getElementById("bmi-weight").value;

		console.log(weightKg);
		if (weightKg !== "") weightPoundsInput.value = Math.ceil(weightKg * 2.20462 * 100) / 100;

		inputBmiWeight.addEventListener("keyup", function() {
			let weightKg = inputBmiWeight.value;
			weightPoundsInput.value = Math.ceil(weightKg * 2.20462 * 100) / 100;
		});



		const bmiResult = document.getElementById("bmi-result").value;
		const voResult = document.getElementById("vo-result").value;

		const bmiCategory = document.getElementById("bmi-category");
		const voCategory = document.getElementById("vo-category");

		if (bmiResult !== "") {
			if (bmiResult < 18.5) {
				category = "Underweight";
				bmiCategory.style.border = "2px solid #03a9f4";
			} else if (bmiResult < 24.9) {
				category = "Normal weight";
				bmiCategory.style.border = "2px solid #4caf50";
			} else if (bmiResult < 29.9) {
				category = "Overweight";
				bmiCategory.style.border = "2px solid #ff9800";
			} else {
				category = "Obese";
				bmiCategory.style.border = "2px solid #f44336";
			}

			bmiCategory.textContent = `BMI Category: ${category}`;
		}

		if (voResult !== "") {
			if (voResult < 25) {
				category = "Very Poor";
				voCategory.style.border = "2px solid #e53935";
			} else if (voResult < 35) {
				category = "Poor";
				voCategory.style.border = "2px solid #fb8c00";
			} else if (voResult < 45) {
				category = "Fair";
				voCategory.style.border = "2px solid #fdd835";
			} else if (voResult < 55) {
				category = "Good";
				voCategory.style.border = "2px solid #43a047";
			} else {
				category = "Excellent";
				voCategory.style.border = "2px solid #1e88e5";
			}

			voCategory.textContent = `Fitness Category: ${category}`;
		}


		const inputNumber = document.querySelectorAll('input[type="number"]');

		const noEInput = function(e) {
			if (e.key.toLowerCase() === "e") {
				e.preventDefault();
			}
		};

		for (let i = 0; i < inputNumber.length; i++) {
			inputNumber[i].addEventListener("keydown", noEInput);
		}
	}

	setValues();
</script>