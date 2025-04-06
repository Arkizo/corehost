<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_SESSION['cliente_id'];
    $plan_id = $_POST['plan_id'] ?? null;
    $disco_id = $_POST['disco_secundario_id'] ?? null;
    $plantilla = $_POST['plantilla_base'] ?? 'Ubuntu';

    if ($plan_id && $disco_id) {
        try {
            // Obtener recursos del plan
            $stmtPlan = $conn->prepare("SELECT cores, ram FROM planes_recursos WHERE id = ?");
            $stmtPlan->execute([$plan_id]);
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

            // Obtener tamaño de disco adicional
            $stmtDisco = $conn->prepare("SELECT cantidad_gb FROM tramos_disco WHERE id = ?");
            $stmtDisco->execute([$disco_id]);
            $disco = $stmtDisco->fetch(PDO::FETCH_ASSOC);

            if ($plan && $disco) {
                // Insertar nueva VM con estado 'pendiente'
                $stmtInsert = $conn->prepare("
                    INSERT INTO vms (cliente_id, estado, cores, memory, hdd, disco_secundario, plantilla_base, plan_id, disco_secundario_id)
                    VALUES (?, 'pendiente', ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtInsert->execute([
                    $cliente_id,
                    $plan['cores'],
                    $plan['ram'],
                    20, // disco base fijo (puedes parametrizarlo también)
                    $disco['cantidad_gb'],
                    $plantilla,
                    $plan_id,
                    $disco_id
                ]);

                $mensaje = 'Tu solicitud ha sido enviada. La máquina se está creando.';
            } else {
                $mensaje = 'Error al obtener los datos del plan o disco.';
            }

        } catch (PDOException $e) {
            $mensaje = 'Error al crear la máquina: ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Debes seleccionar un plan y un disco.';
    }
}

// Obtener opciones de planes y discos
$planes = $conn->query("SELECT * FROM planes_recursos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$discos = $conn->query("SELECT * FROM tramos_disco WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servidor</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="contenedor">
    <h2>Solicitar nuevo servidor</h2>
    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post" action="crear_vm.php">
        <label>Plantilla base:</label>
        <select name="plantilla_base" required>
            <option value="Ubuntu">Ubuntu</option>
            <option value="Debian">Debian</option>
        </select>

        <label>Plan de recursos:</label>
        <select name="plan_id" required>
            <?php foreach ($planes as $plan): ?>
                <option value="<?= $plan['id'] ?>">
                    <?= $plan['nombre'] ?> - <?= $plan['cores'] ?> cores / <?= $plan['ram'] / 1024 ?> GB RAM
                </option>
            <?php endforeach; ?>
        </select>

        <label>Disco adicional (Thin):</label>
        <select name="disco_secundario_id" required>
            <?php foreach ($discos as $disco): ?>
                <option value="<?= $disco['id'] ?>">
                    <?= $disco['cantidad_gb'] ?> GB - <?= $disco['precio'] ?> €
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Crear servidor</button>
    </form>
</div>
</body>
</html>