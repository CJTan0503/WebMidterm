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

// Fetch user details for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $_SESSION['username'] = $user['name'];
    $_SESSION['email'] = $user['email'];
} else {
    echo "<div class='card-panel red lighten-4'>Error: User not found.</div>";
    exit();
}
$stmt->close();

// Handle form submission for BMI calculation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['height']) && isset($_POST['weight'])) {
    $height = (float)$_POST['height'];
    $weight = (float)$_POST['weight'];

    // Validate inputs
    if ($height > 0 && $weight > 0) {
        $bmi = round($weight / (($height / 100) * ($height / 100)), 2);

        if ($bmi < 18.5) $bmiCategory = 'Underweight';
        elseif ($bmi < 24.9) $bmiCategory = 'Normal weight';
        elseif ($bmi < 29.9) $bmiCategory = 'Overweight';
        else $bmiCategory = 'Obesity';

        $stmt = $mysqli->prepare("INSERT INTO bmi_results (user_id, name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddds", $user_id, $_SESSION['username'], $height, $weight, $bmi, $bmiCategory);
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
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $mysqli->prepare("DELETE FROM bmi_results WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    $update_fields = "name = ?, email = ?";
    $params = [$username, $email];
    $types = "ss";

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields .= ", password = ?";
        $params[] = $hashed_password;
        $types .= "s";
    }

    $query = "UPDATE users SET $update_fields WHERE id = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            echo "<div class='card-panel green lighten-4'>Profile updated successfully!</div>";
        } else {
            echo "<div class='card-panel red lighten-4'>Error updating profile: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='card-panel red lighten-4'>Error preparing statement!</div>";
    }
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
</head>
<body>
    <div class="container">
        <h3 class="center-align">BMI Calculator</h3>
        <div class="row">
            <div class="col s12 right-align">
                <a class="btn blue waves-effect waves-light modal-trigger" href="#profileModal">
                    <i class="material-icons left">person</i>Profile
                </a>
                <a href="logout.php" class="btn red waves-effect waves-light">
                    <i class="material-icons left">exit_to_app</i>Logout
                </a>
            </div>
        </div>

        <!-- Profile Modal -->
        <div id="profileModal" class="modal">
            <div class="modal-content">
                <h4>Update Profile</h4>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="username" type="text" name="username" value="<?php echo $_SESSION['username']; ?>" required>
                            <label for="username">Username</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="email" type="email" name="email" value="<?php echo $_SESSION['email']; ?>" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="input-field col s12">
                            <input id="new_password" type="password" name="new_password">
                            <label for="new_password">New Password (leave blank to keep current)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn waves-effect waves-green">Update</button>
                        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- BMI Form -->
        <div class="card z-depth-1" style="padding: 20px;">
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
                <button class="btn waves-effect waves-light col s12" type="submit">Calculate BMI</button>
            </form>
        </div>

        <!-- BMI History -->
        <h5 class="center-align">BMI History</h5>
        <table class="striped centered">
            <thead>
                <tr>
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

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>
                            <td>{$row['height']}</td>
                            <td>{$row['weight']}</td>
                            <td>{$row['bmi']}</td>
                            <td>{$row['category']}</td>
                            <td>
                                <form method='POST'>
                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                    <button type='submit' class='btn-small red waves-effect waves-light'>
                                        <i class='material-icons'>delete</i>
                                    </button>
                                </form>
                            </td>
                        </tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modals = document.querySelectorAll('.modal');
            M.Modal.init(modals);
        });
    </script>
</body>
</html>
