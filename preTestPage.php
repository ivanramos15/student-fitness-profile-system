<?php
session_start();
if (!isset($_SESSION['studentID'])) {
  http_response_code(401);
  exit;
}
include_once "database_conn.php";
include_once "calculateAge.php";

$studentID = $_SESSION['studentID'];

$stmt = $conn->prepare("SELECT * FROM `student` WHERE student_id = ?");
$stmt->bind_param("s", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();



?>


<h2>Let's get started!</h2>
<p class="p-message">Fill out the forms to create your fitness profile.</p>
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
					placeholder="Height in meters"
					required />
				<br />

				<label for="bmi-weight">Enter your weight (in kilograms):</label><br />
				<input
					type="number"
					id="bmi-weight"
					name="bmi-weight"
					step="any"
					placeholder="Weight in kilograms"
					required />
				<br />

				<div id="" class="bmi-result">
					<label for="">BMI Result:</label><br />
					<input
						type="number"
						id="bmi-result"
						name="bmi-result"
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
						readonly />
				</div>
				<p id="vo-category" class="category-container"></p>
			</div>

			<div class="section-container">
				<h2 class="section-title">Skill-Based Fitness Assessment</h2>
				<div class="skill-choices-container">
					<label class="skill-title">Flexibility:</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="4" required />
						Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="3" /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="2" /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="flexibility" value="1" /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Strength:</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="4" required />
						Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="3" /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="2" /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="strength" value="1" /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Agility:</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="4" required />
						Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="3" /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="2" /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="agility" value="1" /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Speed:</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="4" required />
						Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="3" /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="2" /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="speed" value="1" /> Poor</label><br />
				</div>

				<div class="skill-choices-container">
					<label class="skill-title">Endurance:</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="4" required />
						Excellent</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="3" /> Good</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="2" /> Fair</label><br />
					<label class="skill-choices"><input type="radio" name="endurance" value="1" /> Poor</label><br />
				</div>
			</div>

			<input type="submit" class="btn submit-btn" value="Submit">
		</form>
	</div>
</div>
<script>
	function setValues() {
		const inputNumber = document.querySelectorAll('input[type="number"]');

		const noEInput = function(e) {
			if (e.key.toLowerCase() === "e") {
				e.preventDefault();
			}
		};

		for (let i = 0; i < inputNumber.length; i++) {
			inputNumber[i].addEventListener("keydown", noEInput);
		}


		const inputBmiWeight = document.getElementById('bmi-weight');
		const inputWeightPounds = document.getElementById('vo2-weight');



		inputBmiWeight.addEventListener("keyup", function() {
			let weightKg = inputBmiWeight.value;
			inputWeightPounds.value = Math.ceil(weightKg * 2.20462 * 100) / 100;
		});


	}




	setValues();
</script>