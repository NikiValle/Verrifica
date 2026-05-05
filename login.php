<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($username === "valle" && $password === "verifica") {
        $_SESSION['logged'] = true;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Credenziali non valide";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Valle Gym</title>
</head>
<body>
<h2>Login</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <br><br>
    <input type="password" name="password" placeholder="Password" required>
    <br><br>
    <button type="submit">Login</button>
</form>
</body>
</html>