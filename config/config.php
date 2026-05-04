<?php
/**
 * Configuración general del sistema.
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nombre del sistema (se muestra en el header)
define('APP_NAME', 'Sistema de Contabilidad');

// Ruta base del proyecto (ajustar según carpeta en htdocs)
// Si el proyecto está en htdocs/sis_prueba_contable2, dejar así.
define('BASE_URL', '/sis_prueba_contable2');

// Zona horaria
date_default_timezone_set('America/La_Paz');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
