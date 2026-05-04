<?php
require_once __DIR__ . '/includes/auth.php';

// Estadísticas rápidas
$totalCuentas = (int)$conn->query("SELECT COUNT(*) c FROM cuentas")->fetch_assoc()['c'];
$totalComp = (int)$conn->query("SELECT COUNT(*) c FROM comprobantes")->fetch_assoc()['c'];
$totalUsuarios = (int)$conn->query("SELECT COUNT(*) c FROM usuarios")->fetch_assoc()['c'];

$pageTitle = 'Panel principal';
include __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <h1>Bienvenido, <?= e($_SESSION['user_nombre']) ?></h1>
</div>

<div class="card">
    <p>Resumen del sistema:</p>
    <ul>
        <li>Cuentas registradas: <strong><?= $totalCuentas ?></strong></li>
        <li>Comprobantes registrados: <strong><?= $totalComp ?></strong></li>
        <?php if (esAdmin()): ?>
            <li>Usuarios del sistema: <strong><?= $totalUsuarios ?></strong></li>
        <?php endif; ?>
    </ul>
</div>

<div class="modules-grid">
    <a class="module-tile" href="<?= BASE_URL ?>/modules/cuentas/index.php">
        <h3>Plan de cuentas</h3>
        <p>Gestionar cuentas contables (CRUD).</p>
    </a>
    <a class="module-tile" href="<?= BASE_URL ?>/modules/comprobantes/index.php">
        <h3>Comprobantes</h3>
        <p>Registrar asientos contables (debe / haber).</p>
    </a>
    <a class="module-tile" href="<?= BASE_URL ?>/modules/libro_diario/index.php">
        <h3>Libro diario</h3>
        <p>Listado cronológico de comprobantes.</p>
    </a>
    <a class="module-tile" href="<?= BASE_URL ?>/modules/libro_mayor/index.php">
        <h3>Libro mayor</h3>
        <p>Movimientos por cuenta contable.</p>
    </a>
    <a class="module-tile" href="<?= BASE_URL ?>/modules/balance_comprobacion/index.php">
        <h3>Balance de comprobación</h3>
        <p>Sumas de debe y haber por cuenta.</p>
    </a>
    <a class="module-tile" href="<?= BASE_URL ?>/modules/balance_general/index.php">
        <h3>Balance general</h3>
        <p>Activos, pasivos y patrimonio.</p>
    </a>
    <?php if (esAdmin()): ?>
        <a class="module-tile" href="<?= BASE_URL ?>/modules/usuarios/index.php">
            <h3>Usuarios</h3>
            <p>Crear y administrar usuarios y roles.</p>
        </a>
        <a class="module-tile" href="<?= BASE_URL ?>/modules/empresa/index.php">
            <h3>Datos de la empresa</h3>
            <p>Información que aparece en los reportes.</p>
        </a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
