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
<form method="POST">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="username"><br>
</form>