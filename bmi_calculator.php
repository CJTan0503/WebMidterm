<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "bmi_system");

// Check database connection
if ($mysqli->connect_error) {
    die("Database Connection Failed: " . $mysqli->connect_error);
}

// Fetch user name for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = $user['name'];
$stmt->close();

// Handle form submission and BMI calculation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['height']) && isset($_POST['weight'])) {
    $height = (float) $_POST['height'];
    $weight = (float) $_POST['weight'];

    // Validate inputs
    if ($height > 0 && $weight > 0) {
        $bmi = round($weight / (($height / 100) * ($height / 100)), 2);

        if ($bmi < 18.5) $bmiCategory = 'Underweight';
        elseif ($bmi < 24.9) $bmiCategory = 'Normal weight';
        elseif ($bmi < 29.9) $bmiCategory = 'Overweight';
        else $bmiCategory = 'Obesity';

        $stmt = $mysqli->prepare("INSERT INTO bmi_results (user_id, name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddds", $user_id, $name, $height, $weight, $bmi, $bmiCategory);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<div class='card-panel red lighten-4'>Please enter valid height and weight values!</div>";
    }
}

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    $stmt = $mysqli->prepare("DELETE FROM bmi_results WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #fafafa;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .table-header th {
            font-size: 16px;
            font-weight: bold;
        }

        table.striped tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table.striped tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .btn-small {
            background-color: #ff5252;
        }

        .btn-small:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="center-align">BMI Calculator</h3>
        <div class="card z-depth-1" style="padding: 20px; border-radius: 10px;">
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
                <div class="row center-align">
                    <button class="btn waves-effect waves-light col s12" type="submit" name="action">
                        Calculate BMI
                        <i class="material-icons right">calculate</i>
                    </button>
                </div>
            </form>
        </div>

        <h5 class="center-align">BMI History</h5>
        <table class="striped centered">
            <thead>
                <tr class="table-header">
                    <th>Date</th>
                    <th>Height (cm)</th>
                    <th>Weight (kg)</th>
                    <th>BMI</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $mysqli->prepare("SELECT * FROM bmi_results WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                        echo "<td>{$row['height']}</td>";
                        echo "<td>{$row['weight']}</td>";
                        echo "<td>{$row['bmi']}</td>";
                        echo "<td>{$row['category']}</td>";
                        echo "<td>
                                <form method='POST' style='margin:0;' onsubmit='return confirm(\"Are you sure you want to delete this record?\")'>
                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                    <button type='submit' class='btn-small waves-effect waves-light'>
                                        <i class='material-icons'>delete</i>
                                    </button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No BMI history available.</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
