<?php
/**
 * Conexión a la base de datos MariaDB / MySQL.
 *
 * Ajusta estos valores si tu entorno local los requiere.
 * En XAMPP por defecto: host=localhost, user=root, password='' (vacío).
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'conta_prueba2';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die('Error de conexión a la base de datos: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
