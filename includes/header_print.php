<?php
require_once __DIR__ . '/../config/config.php';
$empresa = obtenerEmpresa($conn);
$pageTitle = $pageTitle ?? 'Reporte';
$reportTitle = $reportTitle ?? 'Reporte';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e($reportTitle) ?> | <?= e($empresa['nombre']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/print.css">
</head>
<body class="print-page">
<div class="print-toolbar no-print">
    <button onclick="window.print()">Imprimir</button>
    <button onclick="window.close()">Cerrar</button>
</div>
<div class="report">
    <div class="report-header">
        <h1><?= e($empresa['nombre']) ?></h1>
        <div class="empresa-info">
            <div>NIT: <?= e($empresa['nit']) ?></div>
            <div><?= e($empresa['direccion']) ?></div>
            <div>Tel: <?= e($empresa['telefono']) ?></div>
        </div>
        <h2 class="report-title"><?= e($reportTitle) ?></h2>
        <div class="report-date">Fecha de impresión: <?= date('d/m/Y H:i') ?></div>
    </div>
    <div class="report-body">
