<?php
require_once __DIR__ . '/../../includes/auth.php';

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = '';
$params = [];
$types = '';

if ($desde !== '' && $hasta !== '') {
    $where = 'WHERE c.fecha BETWEEN ? AND ?';
    $params = [$desde, $hasta];
    $types = 'ss';
} elseif ($desde !== '') {
    $where = 'WHERE c.fecha >= ?';
    $params = [$desde];
    $types = 's';
} elseif ($hasta !== '') {
    $where = 'WHERE c.fecha <= ?';
    $params = [$hasta];
    $types = 's';
}

$sql = "
    SELECT c.id, c.numero, c.fecha, c.descripcion,
           d.debe, d.haber, ct.codigo, ct.nombre
    FROM comprobantes c
    JOIN comprobante_detalles d ON d.comprobante_id = c.id
    JOIN cuentas ct ON ct.id = d.cuenta_id
    $where
    ORDER BY c.fecha ASC, c.id ASC, d.id ASC
";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();

$reportTitle = 'Libro Diario'
    . ($desde !== '' ? ' (desde ' . $desde . ')' : '')
    . ($hasta !== '' ? ' (hasta ' . $hasta . ')' : '');

include __DIR__ . '/../../includes/header_print.php';
?>
<table class="table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Comp.</th>
            <th>Código</th>
            <th>Cuenta</th>
            <th>Glosa</th>
            <th class="text-right">Debe</th>
            <th class="text-right">Haber</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $totalDebe = 0; $totalHaber = 0;
    $lastComp = null;
    while ($r = $rows->fetch_assoc()):
        $totalDebe += $r['debe'];
        $totalHaber += $r['haber'];
        $sameComp = ($lastComp === $r['numero']);
        $lastComp = $r['numero'];
    ?>
        <tr>
            <td><?= $sameComp ? '' : e($r['fecha']) ?></td>
            <td><?= $sameComp ? '' : e($r['numero']) ?></td>
            <td><?= e($r['codigo']) ?></td>
            <td><?= e($r['nombre']) ?></td>
            <td><?= $sameComp ? '' : e($r['descripcion']) ?></td>
            <td class="text-right"><?= $r['debe'] > 0 ? money($r['debe']) : '' ?></td>
            <td class="text-right"><?= $r['haber'] > 0 ? money($r['haber']) : '' ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Totales</th>
            <th class="text-right"><?= money($totalDebe) ?></th>
            <th class="text-right"><?= money($totalHaber) ?></th>
        </tr>
    </tfoot>
</table>

<?php include __DIR__ . '/../../includes/footer_print.php'; ?>
