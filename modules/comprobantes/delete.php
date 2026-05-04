<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM comprobantes WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    flashSet('success', 'Comprobante eliminado.');
} else {
    flashSet('error', 'No se pudo eliminar el comprobante.');
}
$stmt->close();
header('Location: index.php');
exit;
