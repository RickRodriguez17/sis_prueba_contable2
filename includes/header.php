<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = $pageTitle ?? APP_NAME;
$flash = flashGet();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/print.css" media="print">
</head>
<body>
<?php if (!empty($_SESSION['user_id'])): ?>
<header class="topbar no-print">
    <div class="topbar-inner">
        <a class="brand" href="<?= BASE_URL ?>/dashboard.php"><?= e(APP_NAME) ?></a>
        <nav class="menu">
            <a href="<?= BASE_URL ?>/dashboard.php">Inicio</a>
            <a href="<?= BASE_URL ?>/modules/cuentas/index.php">Plan de cuentas</a>
            <a href="<?= BASE_URL ?>/modules/comprobantes/index.php">Comprobantes</a>
            <a href="<?= BASE_URL ?>/modules/libro_diario/index.php">Libro diario</a>
            <a href="<?= BASE_URL ?>/modules/libro_mayor/index.php">Libro mayor</a>
            <a href="<?= BASE_URL ?>/modules/balance_comprobacion/index.php">Bal. comprobación</a>
            <a href="<?= BASE_URL ?>/modules/balance_general/index.php">Bal. general</a>
            <?php if (esAdmin()): ?>
                <a href="<?= BASE_URL ?>/modules/usuarios/index.php">Usuarios</a>
                <a href="<?= BASE_URL ?>/modules/empresa/index.php">Empresa</a>
            <?php endif; ?>
        </nav>
        <div class="user-box">
            <span><?= e($_SESSION['user_nombre'] ?? '') ?> (<?= e($_SESSION['user_rol'] ?? '') ?>)</span>
            <a class="btn-logout" href="<?= BASE_URL ?>/logout.php">Salir</a>
        </div>
    </div>
</header>
<?php endif; ?>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
