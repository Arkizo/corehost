<?php
require_once 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $password]);
            $mensaje = 'Registro exitoso. Ya puedes iniciar sesión.';
        } catch (PDOException $e) {
            $mensaje = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Por favor, completa todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cliente</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <h2>Registro</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="post" action="registro.php">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
    </div>
</body>
</html>
