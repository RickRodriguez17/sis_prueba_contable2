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
    echo "Comprobante no encontrado.";
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

$reportTitle = 'Comprobante ' . $comp['numero'];
include __DIR__ . '/../../includes/header_print.php';
?>
<div style="margin-bottom:10px">
    <strong>Número:</strong> <?= e($comp['numero']) ?> &nbsp; | &nbsp;
    <strong>Fecha:</strong> <?= e($comp['fecha']) ?> &nbsp; | &nbsp;
    <strong>Usuario:</strong> <?= e($comp['usuario'] ?? '-') ?>
</div>
<div style="margin-bottom:14px">
    <strong>Descripción:</strong> <?= e($comp['descripcion']) ?>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width:120px">Código</th>
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

<div style="margin-top:60px;display:flex;justify-content:space-around;text-align:center">
    <div>
        <hr style="border-top:1px solid #000;width:200px">
        Elaborado por
    </div>
    <div>
        <hr style="border-top:1px solid #000;width:200px">
        Revisado por
    </div>
    <div>
        <hr style="border-top:1px solid #000;width:200px">
        Autorizado por
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer_print.php'; ?>
