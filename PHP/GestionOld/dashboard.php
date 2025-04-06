<?php
session_start();
require_once 'conexion.php';

// Validar sesión o cookie
if (!isset($_SESSION['cliente_id']) && isset($_COOKIE['cliente_id'])) {
    $_SESSION['cliente_id'] = $_COOKIE['cliente_id'];
}

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$mensaje = '';

// Gestión de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar'])) {
        $vmid = intval($_POST['eliminar']);
        $stmt = $conn->prepare("UPDATE vms SET estado = 'eliminada' WHERE vmid = ? AND cliente_id = ?");
        $stmt->bind_param("ii", $vmid, $cliente_id);
        $stmt->execute();
        $mensaje = "VM #$vmid marcada como eliminada.";
    }
    if (isset($_POST['reiniciar'])) {
        $vmid = intval($_POST['reiniciar']);
        // Aquí puedes lanzar el playbook o script que reinicie la VM
        $mensaje = "Orden de reinicio enviada para la VM #$vmid.";
    }
}

// Obtener VMs del cliente
$stmt = $conn->prepare("SELECT vmid, hostname, ip_publica, ip_privada, estado FROM vms WHERE cliente_id = ? AND estado != 'eliminada'");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$vms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<?php include 'header.php'; ?>
<div class="dashboard">
  <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['cliente_nombre'] ?? ''); ?>!</h2>
  <p><a href="crear_vm.php">Solicitar nueva VM</a> | <a href="logout.php">Cerrar sesión</a></p>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <h3>Mis máquinas virtuales</h3>
  <?php if (empty($vms)): ?>
    <p>No tienes ninguna VM activa.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>VMID</th>
          <th>Hostname</th>
          <th>IP Pública</th>
          <th>IP Privada</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vms as $vm): ?>
          <tr>
            <td><?php echo $vm['vmid']; ?></td>
            <td><?php echo htmlspecialchars($vm['hostname']); ?></td>
            <td><?php echo htmlspecialchars($vm['ip_publica']); ?></td>
            <td><?php echo htmlspecialchars($vm['ip_privada']); ?></td>
            <td><?php echo htmlspecialchars($vm['estado']); ?></td>
            <td>
              <form method="post" style="display:inline">
                <button type="submit" name="eliminar" value="<?php echo $vm['vmid']; ?>">Eliminar</button>
              </form>
              <form method="post" style="display:inline">
                <button type="submit" name="reiniciar" value="<?php echo $vm['vmid']; ?>">Reiniciar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>