<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Balance de comprobación';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = '';
$params = [];
$types = '';
if ($desde !== '' && $hasta !== '') {
    $where = 'WHERE c.fecha BETWEEN ? AND ?';
    $params = [$desde, $hasta]; $types = 'ss';
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

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Balance de comprobación</h1>
    <a class="btn btn-print" target="_blank"
       href="print.php?desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>">Imprimir</a>
</div>

<div class="card">
    <form method="get" class="form-row" style="align-items:flex-end">
        <div class="form-group" style="max-width:200px">
            <label>Desde</label>
            <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>">
        </div>
        <div class="form-group" style="max-width:200px">
            <label>Hasta</label>
            <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>">
        </div>
        <div class="form-group">
            <button class="btn">Filtrar</button>
            <a class="btn btn-secondary" href="index.php">Limpiar</a>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th>Tipo</th>
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
                <td><?= e(tipoCuentaLabel($r['tipo'])) ?></td>
                <td class="text-right"><?= money($debe) ?></td>
                <td class="text-right"><?= money($haber) ?></td>
                <td class="text-right"><?= $saldoDeudor>0?money($saldoDeudor):'' ?></td>
                <td class="text-right"><?= $saldoAcreedor>0?money($saldoAcreedor):'' ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Totales</th>
                <th class="text-right"><?= money($totalDebe) ?></th>
                <th class="text-right"><?= money($totalHaber) ?></th>
                <th class="text-right"><?= money($totalSD) ?></th>
                <th class="text-right"><?= money($totalSA) ?></th>
            </tr>
        </tfoot>
    </table>
    <p class="help-text">El total del Debe debe ser igual al total del Haber, y el Saldo Deudor al Saldo Acreedor.</p>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
