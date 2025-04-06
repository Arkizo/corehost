<?php
session_start();
require_once 'conexion.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $recordarme = isset($_POST['recordarme']);

    $stmt = $conn->prepare("SELECT id, nombre, password_hash FROM clientes WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        if (password_verify($password, $cliente['password_hash'])) {
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];

            if ($recordarme) {
                setcookie("cliente_id", $cliente['id'], time() + (86400 * 30), "/");
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $errores[] = "Contraseña incorrecta";
        }
    } else {
        $errores[] = "Usuario no encontrado";
    }
}
?>

<?php include 'header.php'; ?>
<div class="formulario">
  <h2>Inicio de Sesión</h2>
  <?php if (!empty($errores)): ?>
    <div class="errores">
      <ul>
        <?php foreach ($errores as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <label><input type="checkbox" name="recordarme"> Recordarme</label>
    <button type="submit">Iniciar sesión</button>
  </form>
  <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</div>
<?php include 'footer.php'; ?>
