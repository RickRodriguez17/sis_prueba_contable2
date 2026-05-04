<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Comprobantes';

$res = $conn->query("
    SELECT c.id, c.numero, c.fecha, c.descripcion, c.total_debe, c.total_haber,
           u.nombre AS usuario
    FROM comprobantes c
    LEFT JOIN usuarios u ON u.id = c.usuario_id
    ORDER BY c.fecha DESC, c.id DESC
");

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Comprobantes contables</h1>
    <a class="btn btn-success" href="create.php">+ Nuevo comprobante</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th class="text-right">Debe</th>
                <th class="text-right">Haber</th>
                <th>Usuario</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($c = $res->fetch_assoc()): ?>
            <tr>
                <td><?= e($c['numero']) ?></td>
                <td><?= e($c['fecha']) ?></td>
                <td><?= e($c['descripcion']) ?></td>
                <td class="text-right"><?= money($c['total_debe']) ?></td>
                <td class="text-right"><?= money($c['total_haber']) ?></td>
                <td><?= e($c['usuario'] ?? '-') ?></td>
                <td class="text-center">
                    <a class="btn btn-sm" href="view.php?id=<?= (int)$c['id'] ?>">Ver</a>
                    <a class="btn btn-sm btn-print" target="_blank" href="print.php?id=<?= (int)$c['id'] ?>">Imprimir</a>
                    <a class="btn btn-sm btn-danger"
                       href="delete.php?id=<?= (int)$c['id'] ?>"
                       onclick="return confirm('¿Eliminar este comprobante?');">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
