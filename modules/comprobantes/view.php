<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT c.*, u.nombre AS usuario
    FROM comprobantes c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    WHERE c.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$comp) {
    flashSet('error', 'Comprobante no encontrado.');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT d.debe, d.haber, ct.codigo, ct.nombre
    FROM comprobante_detalles d
    JOIN cuentas ct ON ct.id = d.cuenta_id
    WHERE d.comprobante_id = ?
    ORDER BY d.id
");
$stmt->bind_param('i', $id);
$stmt->execute();
$detalles = $stmt->get_result();

$pageTitle = 'Comprobante ' . $comp['numero'];
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Comprobante <?= e($comp['numero']) ?></h1>
    <div>
        <a class="btn btn-print" target="_blank" href="print.php?id=<?= (int)$comp['id'] ?>">Imprimir</a>
        <a class="btn btn-secondary" href="index.php">Volver</a>
    </div>
</div>

<div class="card">
    <div class="form-row">
        <div class="form-group"><label>Fecha</label><div><?= e($comp['fecha']) ?></div></div>
        <div class="form-group"><label>Descripción</label><div><?= e($comp['descripcion']) ?></div></div>
        <div class="form-group"><label>Usuario</label><div><?= e($comp['usuario'] ?? '-') ?></div></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:130px">Código</th>
                <th>Cuenta</th>
                <th class="text-right">Debe</th>
                <th class="text-right">Haber</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($d = $detalles->fetch_assoc()): ?>
            <tr>
                <td><?= e($d['codigo']) ?></td>
                <td><?= e($d['nombre']) ?></td>
                <td class="text-right"><?= money($d['debe']) ?></td>
                <td class="text-right"><?= money($d['haber']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Totales</th>
                <th class="text-right"><?= money($comp['total_debe']) ?></th>
                <th class="text-right"><?= money($comp['total_haber']) ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
