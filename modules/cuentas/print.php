<?php
require_once __DIR__ . '/../../includes/auth.php';

$tipoFiltro = $_GET['tipo'] ?? '';
$tiposValidos = ['activo','pasivo','patrimonio','ingreso','gasto'];

if ($tipoFiltro !== '' && in_array($tipoFiltro, $tiposValidos, true)) {
    $stmt = $conn->prepare("SELECT * FROM cuentas WHERE tipo = ? ORDER BY codigo");
    $stmt->bind_param('s', $tipoFiltro);
    $stmt->execute();
    $cuentas = $stmt->get_result();
    $reportTitle = 'Plan de cuentas - ' . tipoCuentaLabel($tipoFiltro);
} else {
    $cuentas = $conn->query("SELECT * FROM cuentas ORDER BY codigo");
    $reportTitle = 'Plan de cuentas';
}

include __DIR__ . '/../../includes/header_print.php';
?>
<table class="table">
    <thead>
        <tr>
            <th style="width:120px">Código</th>
            <th>Nombre</th>
            <th style="width:140px">Tipo</th>
            <th style="width:90px" class="text-center">Activa</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($c = $cuentas->fetch_assoc()): ?>
        <tr>
            <td><?= e($c['codigo']) ?></td>
            <td><?= e($c['nombre']) ?></td>
            <td><?= e(tipoCuentaLabel($c['tipo'])) ?></td>
            <td class="text-center"><?= ((int)$c['activo']===1)?'Sí':'No' ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer_print.php'; ?>
