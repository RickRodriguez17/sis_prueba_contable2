<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Libro diario';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = '';
$params = [];
$types = '';

if ($desde !== '' && $hasta !== '') {
    $where = 'WHERE c.fecha BETWEEN ? AND ?';
    $params[] = $desde;
    $params[] = $hasta;
    $types = 'ss';
} elseif ($desde !== '') {
    $where = 'WHERE c.fecha >= ?';
    $params[] = $desde;
    $types = 's';
} elseif ($hasta !== '') {
    $where = 'WHERE c.fecha <= ?';
    $params[] = $hasta;
    $types = 's';
}

$sql = "
    SELECT c.id, c.numero, c.fecha, c.descripcion, c.total_debe, c.total_haber,
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

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Libro diario</h1>
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
            <button class="btn" type="submit">Filtrar</button>
            <a class="btn btn-secondary" href="index.php">Limpiar</a>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
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
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
