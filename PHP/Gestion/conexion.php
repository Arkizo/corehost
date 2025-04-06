<?php
$servername = "172.16.56.145";
$username = "hostinguser"; // o el que uses
$password = "proyecto"; // cámbialo por el real
$dbname = "hosting_inventari";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
