<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

$cliente_id = $_SESSION['cliente_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM vms WHERE cliente_id = :cliente_id ORDER BY fecha_creacion DESC");
    $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
    $stmt->execute();
    $vms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las VMs: " . $e->getMessage());
}
?>

<?php include 'header.php'; ?>
<div class="dashboard">
  <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></h2>
  <p><a href="crear_vm.php">Crear nueva VM</a> | <a href="logout.php">Cerrar sesión</a></p>

  <h3>Tus máquinas virtuales</h3>

  <?php if (!empty($vms)): ?>
    <table>
      <thead>
        <tr>
          <th>Hostname</th>
          <th>IP Pública</th>
          <th>IP Privada</th>
          <th>Estado</th>
          <th>Recursos</th>
          <th>Disco</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vms as $vm): ?>
          <tr>
            <td><?php echo htmlspecialchars($vm['hostname']); ?></td>
            <td><?php echo htmlspecialchars($vm['ip_publica']); ?></td>
            <td><?php echo htmlspecialchars($vm['ip_privada']); ?></td>
            <td><?php echo htmlspecialchars($vm['estado']); ?></td>
            <td><?php echo $vm['cores'] . ' cores / ' . $vm['memory'] . ' MB'; ?></td>
            <td><?php echo $vm['disco_secundario'] . ' GB'; ?></td>
            <td>
              <?php if ($vm['estado'] == 'completado'): ?>
                <form method="post" action="acciones_vm.php">
                  <input type="hidden" name="vmid" value="<?php echo $vm['vmid']; ?>">
                  <button name="accion" value="reiniciar">Reiniciar</button>
                  <button name="accion" value="eliminar" onclick="return confirm('¿Estás seguro de eliminar esta VM?')">Eliminar</button>
                </form>
              <?php else: ?>
                <em>No disponible</em>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No tienes máquinas virtuales todavía.</p>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
