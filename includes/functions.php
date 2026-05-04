<?php
/**
 * Funciones auxiliares del sistema.
 */

/**
 * Sanitiza una cadena para imprimirla en HTML.
 */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatea un número como moneda (con separador de miles y 2 decimales).
 */
function money($value) {
    return number_format((float)$value, 2, '.', ',');
}

/**
 * Devuelve los datos de la empresa (siempre la fila id=1).
 */
function obtenerEmpresa($conn) {
    $res = $conn->query("SELECT * FROM empresa WHERE id = 1 LIMIT 1");
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc();
    }
    return [
        'nombre' => 'Mi Empresa',
        'nit' => '-',
        'direccion' => '-',
        'telefono' => '-'
    ];
}

/**
 * Texto legible del tipo de cuenta.
 */
function tipoCuentaLabel($tipo) {
    $map = [
        'activo'     => 'Activo',
        'pasivo'     => 'Pasivo',
        'patrimonio' => 'Patrimonio',
        'ingreso'    => 'Ingreso',
        'gasto'      => 'Gasto',
    ];
    return $map[$tipo] ?? ucfirst($tipo);
}

/**
 * Guarda un mensaje flash en sesión (para mostrarlo tras un redirect).
 */
function flashSet($tipo, $mensaje) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Obtiene y limpia el mensaje flash actual.
 */
function flashGet() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Genera el siguiente número de comprobante (CMP-0001, CMP-0002, ...).
 */
function siguienteNumeroComprobante($conn) {
    $res = $conn->query("SELECT numero FROM comprobantes ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $last = $res->fetch_assoc()['numero'];
        if (preg_match('/CMP-(\d+)/', $last, $m)) {
            $n = (int)$m[1] + 1;
            return 'CMP-' . str_pad($n, 4, '0', STR_PAD_LEFT);
        }
    }
    return 'CMP-0001';
}

/**
 * Valida que un código contable cumpla: solo dígitos y exactamente 8 caracteres.
 */
function validarCodigoCuenta($codigo) {
    return (bool)preg_match('/^[0-9]{8}$/', (string)$codigo);
}
