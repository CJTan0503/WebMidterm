<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "bmi_system");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: bmi_calculator.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with this email!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        <h4 class="center-align">Login</h4>
        <?php if (isset($error)) echo "<div class='card-panel red lighten-4'>$error</div>"; ?>
        <div class="row z-depth-1" style="padding: 20px; border-radius: 10px;">
            <form method="POST" action="">
                <div class="input-field">
                    <input id="email" type="email" name="email" required>
                    <label for="email">Email</label>
                </div>
                <div class="input-field">
                    <input id="password" type="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                <button class="btn waves-effect waves-light col s12" type="submit">Login</button>
            </form>
            <div class="row center-align" style="margin-top: 20px;">
                <a href="registration.php" class="btn-flat waves-effect">Don't have an account? Register</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
