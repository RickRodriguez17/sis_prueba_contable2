<?php
require_once __DIR__ . '/../../includes/auth.php';

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = '';
$params = [];
$types = '';
if ($desde !== '' && $hasta !== '') {
    $where = 'WHERE c.fecha BETWEEN ? AND ?'; $params = [$desde, $hasta]; $types = 'ss';
} elseif ($desde !== '') {
    $where = 'WHERE c.fecha >= ?'; $params = [$desde]; $types = 's';
} elseif ($hasta !== '') {
    $where = 'WHERE c.fecha <= ?'; $params = [$hasta]; $types = 's';
}

$sql = "
    SELECT ct.codigo, ct.nombre, ct.tipo,
           SUM(d.debe) AS total_debe,
           SUM(d.haber) AS total_haber
    FROM cuentas ct
    JOIN comprobante_detalles d ON d.cuenta_id = ct.id
    JOIN comprobantes c ON c.id = d.comprobante_id
    $where
    GROUP BY ct.id, ct.codigo, ct.nombre, ct.tipo
    ORDER BY ct.codigo
";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();

$reportTitle = 'Balance de Comprobación'
    . ($desde !== '' ? ' (desde ' . $desde . ')' : '')
    . ($hasta !== '' ? ' (hasta ' . $hasta . ')' : '');

include __DIR__ . '/../../includes/header_print.php';
?>
<table class="table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Cuenta</th>
            <th class="text-right">Sumas Debe</th>
            <th class="text-right">Sumas Haber</th>
            <th class="text-right">Saldo Deudor</th>
            <th class="text-right">Saldo Acreedor</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $totalDebe=0; $totalHaber=0; $totalSD=0; $totalSA=0;
    while ($r = $rows->fetch_assoc()):
        $debe = (float)$r['total_debe'];
        $haber = (float)$r['total_haber'];
        $saldoDeudor = max(0, $debe - $haber);
        $saldoAcreedor = max(0, $haber - $debe);
        $totalDebe += $debe;
        $totalHaber += $haber;
        $totalSD += $saldoDeudor;
        $totalSA += $saldoAcreedor;
    ?>
        <tr>
            <td><?= e($r['codigo']) ?></td>
            <td><?= e($r['nombre']) ?></td>
            <td class="text-right"><?= money($debe) ?></td>
            <td class="text-right"><?= money($haber) ?></td>
            <td class="text-right"><?= $saldoDeudor>0?money($saldoDeudor):'' ?></td>
            <td class="text-right"><?= $saldoAcreedor>0?money($saldoAcreedor):'' ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-right">Totales</th>
            <th class="text-right"><?= money($totalDebe) ?></th>
            <th class="text-right"><?= money($totalHaber) ?></th>
            <th class="text-right"><?= money($totalSD) ?></th>
            <th class="text-right"><?= money($totalSA) ?></th>
        </tr>
    </tfoot>
</table>

<?php include __DIR__ . '/../../includes/footer_print.php'; ?>
