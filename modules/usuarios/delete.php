<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if ($id === (int)$_SESSION['user_id']) {
    flashSet('error', 'No puedes eliminar tu propio usuario.');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    flashSet('success', 'Usuario eliminado.');
} else {
    flashSet('error', 'No se pudo eliminar el usuario (puede tener comprobantes asociados).');
}
$stmt->close();
header('Location: index.php');
exit;
