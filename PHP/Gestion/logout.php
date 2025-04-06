<?php
session_start();

// Eliminar variables de sesión
$_SESSION = [];
session_destroy();

// Eliminar cookie si existe
if (isset($_COOKIE['cliente_id'])) {
    setcookie("cliente_id", "", time() - 3600, "/");
}

header("Location: login.php");
exit();