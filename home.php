<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="center-align">BMI Calculator</h3>
        <div class="row z-depth-1" style="padding: 20px; border-radius: 10px;">
            <form method="POST" action="">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="height" type="number" name="height" step="0.01" required>
                        <label for="height">Height (in cm)</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="weight" type="number" name="weight" step="0.01" required>
                        <label for="weight">Weight (in kg)</label>
                    </div>
                </div>
                <div class="row">
                    <button class="btn waves-effect waves-light col s12" type="submit" name="action">
                        Calculate BMI
                        <i class="material-icons right">calculate</i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Display Results -->
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize and validate user input
            $height = isset($_POST['height']) ? (float) $_POST['height'] : 0;
            $weight = isset($_POST['weight']) ? (float) $_POST['weight'] : 0;

            if ($height <= 0 || $weight <= 0) {
                echo "<div class='card-panel red lighten-4'><strong>Error:</strong> Height and weight must be greater than zero.</div>";
            } else {
                // Calculate BMI
                $bmi = round($weight / (($height / 100) * ($height / 100)), 2);

                // Determine category and color
                if ($bmi < 18.5) {
                    $bmiCategory = 'Underweight';
                    $color = 'blue lighten-4';
                } elseif ($bmi < 24.9) {
                    $bmiCategory = 'Normal weight';
                    $color = 'green lighten-4';
                } elseif ($bmi < 29.9) {
                    $bmiCategory = 'Overweight';
                    $color = 'amber lighten-4';
                } else {
                    $bmiCategory = 'Obesity';
                    $color = 'red lighten-4';
                }

                // Display results
                echo "<div class='card-panel $color'>
                        <h5>Your BMI Results</h5>
                        <p><strong>Height:</strong> $height cm</p>
                        <p><strong>Weight:</strong> $weight kg</p>
                        <p><strong>BMI:</strong> $bmi</p>
                        <p><strong>Category:</strong> $bmiCategory</p>
                    </div>";
            }
        }
        ?>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>