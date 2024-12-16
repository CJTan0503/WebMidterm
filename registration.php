<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "bmi_system");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO users (name, age, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $name, $age, $email, $password);
    
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: Email already exists!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 400px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h4 class="center-align">Register</h4>
        <?php if (isset($error)) echo "<div class='card-panel red lighten-4'>$error</div>"; ?>
        <div class="row z-depth-1" style="padding: 20px; border-radius: 10px;">
            <form method="POST" action="">
                <div class="input-field">
                    <input id="name" type="text" name="name" required>
                    <label for="name">Name</label>
                </div>
                <div class="input-field">
                    <input id="age" type="number" name="age" required>
                    <label for="age">Age</label>
                </div>
                <div class="input-field">
                    <input id="email" type="email" name="email" required>
                    <label for="email">Email</label>
                </div>
                <div class="input-field">
                    <input id="password" type="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                <button class="btn waves-effect waves-light col s12" type="submit">Register</button>
            </form>
            <div class="row center-align" style="margin-top: 20px;">
                <a href="login.php" class="btn-flat waves-effect">Back to Login</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
