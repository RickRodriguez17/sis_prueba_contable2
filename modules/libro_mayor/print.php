<?php
require_once __DIR__ . '/../../includes/auth.php';

$cuentaId = (int)($_GET['cuenta_id'] ?? 0);
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

if ($cuentaId === 0) {
    echo 'Cuenta no especificada.';
    exit;
}

$stmt = $conn->prepare("SELECT * FROM cuentas WHERE id = ?");
$stmt->bind_param('i', $cuentaId);
$stmt->execute();
$cuenta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cuenta) {
    echo 'Cuenta no encontrada.';
    exit;
}

$where = 'WHERE d.cuenta_id = ?';
$params = [$cuentaId];
$types = 'i';
if ($desde !== '') { $where .= ' AND c.fecha >= ?'; $params[] = $desde; $types .= 's'; }
if ($hasta !== '') { $where .= ' AND c.fecha <= ?'; $params[] = $hasta; $types .= 's'; }

$stmt = $conn->prepare("
    SELECT c.fecha, c.numero, c.descripcion, d.debe, d.haber
    FROM comprobante_detalles d
    JOIN comprobantes c ON c.id = d.comprobante_id
    $where
    ORDER BY c.fecha ASC, c.id ASC, d.id ASC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$movs = $stmt->get_result();

$reportTitle = 'Libro Mayor: ' . $cuenta['codigo'] . ' - ' . $cuenta['nombre'];
include __DIR__ . '/../../includes/header_print.php';
?>
<div style="margin-bottom:8px">
    <strong>Cuenta:</strong> <?= e($cuenta['codigo']) ?> - <?= e($cuenta['nombre']) ?>
    &nbsp; | &nbsp; <strong>Tipo:</strong> <?= e(tipoCuentaLabel($cuenta['tipo'])) ?>
    <?php if ($desde): ?> &nbsp; | &nbsp; <strong>Desde:</strong> <?= e($desde) ?><?php endif; ?>
    <?php if ($hasta): ?> &nbsp; | &nbsp; <strong>Hasta:</strong> <?= e($hasta) ?><?php endif; ?>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Comprobante</th>
            <th>Glosa</th>
            <th class="text-right">Debe</th>
            <th class="text-right">Haber</th>
            <th class="text-right">Saldo</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $saldo = 0;
    $totalDebe = 0;
    $totalHaber = 0;
    $deudor = in_array($cuenta['tipo'], ['activo','gasto'], true);
    while ($m = $movs->fetch_assoc()):
        $totalDebe += $m['debe'];
        $totalHaber += $m['haber'];
        $saldo += $deudor ? ($m['debe'] - $m['haber']) : ($m['haber'] - $m['debe']);
    ?>
        <tr>
            <td><?= e($m['fecha']) ?></td>
            <td><?= e($m['numero']) ?></td>
            <td><?= e($m['descripcion']) ?></td>
            <td class="text-right"><?= $m['debe']>0?money($m['debe']):'' ?></td>
            <td class="text-right"><?= $m['haber']>0?money($m['haber']):'' ?></td>
            <td class="text-right"><?= money($saldo) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Totales</th>
            <th class="text-right"><?= money($totalDebe) ?></th>
            <th class="text-right"><?= money($totalHaber) ?></th>
            <th class="text-right"><?= money($saldo) ?></th>
        </tr>
    </tfoot>
</table>

<?php include __DIR__ . '/../../includes/footer_print.php'; ?>
