<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

// No permitir eliminar si está usada en algún comprobante
$stmt = $conn->prepare("SELECT COUNT(*) c FROM comprobante_detalles WHERE cuenta_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$count = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

if ($count > 0) {
    flashSet('error', 'No se puede eliminar: la cuenta está usada en comprobantes. Desactívala en lugar de eliminarla.');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM cuentas WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    flashSet('success', 'Cuenta eliminada.');
} else {
    flashSet('error', 'No se pudo eliminar la cuenta.');
}
$stmt->close();
header('Location: index.php');
exit;
