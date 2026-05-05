<?php
    try {
        $conn = new PDO("mysql:host=$host", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("USE $dbname");
    }catch(PDOException $e){

    }
?>