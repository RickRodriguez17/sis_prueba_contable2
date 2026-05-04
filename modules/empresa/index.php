<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$pageTitle = 'Datos de la empresa';
$errors = [];

// Asegurar que exista la fila id=1
$res = $conn->query("SELECT * FROM empresa WHERE id = 1 LIMIT 1");
if (!$res || $res->num_rows === 0) {
    $conn->query("INSERT INTO empresa (id, nombre, nit, direccion, telefono) VALUES (1, 'Mi Empresa', '0', '', '')");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $nit       = trim($_POST['nit'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');

    if ($nombre === '') $errors[] = 'El nombre de la empresa es obligatorio.';
    if ($nit === '')    $errors[] = 'El NIT es obligatorio.';

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE empresa SET nombre=?, nit=?, direccion=?, telefono=? WHERE id=1");
        $stmt->bind_param('ssss', $nombre, $nit, $direccion, $telefono);
        if ($stmt->execute()) {
            flashSet('success', 'Datos de la empresa actualizados.');
            header('Location: index.php');
            exit;
        }
        $errors[] = 'No se pudo actualizar: ' . $conn->error;
    }
}

$empresa = obtenerEmpresa($conn);
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Datos de la empresa</h1></div>

<div class="card">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Nombre de la empresa</label>
                <input type="text" name="nombre" class="form-control" value="<?= e($empresa['nombre']) ?>" required>
            </div>
            <div class="form-group">
                <label>NIT / Identificación</label>
                <input type="text" name="nit" class="form-control" value="<?= e($empresa['nit']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control" value="<?= e($empresa['direccion']) ?>">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= e($empresa['telefono']) ?>">
            </div>
        </div>
        <button class="btn btn-success" type="submit">Guardar cambios</button>
    </form>
</div>

<p class="help-text">Estos datos aparecerán en el encabezado de todos los reportes imprimibles.</p>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
