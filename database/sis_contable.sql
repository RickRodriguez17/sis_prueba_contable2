-- ============================================================
-- Sistema de Contabilidad Básico
-- Base de datos: sis_contable
-- Motor: MariaDB / MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS sis_contable
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sis_contable;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS comprobante_detalles;
DROP TABLE IF EXISTS comprobantes;
DROP TABLE IF EXISTS cuentas;
DROP TABLE IF EXISTS empresa;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('administrador','contador') NOT NULL DEFAULT 'contador',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario administrador por defecto: admin / admin123
INSERT INTO usuarios (username, password, nombre, rol) VALUES
('admin', '$2y$10$izarKMiJmTAk9grMNLhb2eOdxKhIHloqE2kXFUEP9Vye72K9nizxW', 'Administrador del Sistema', 'administrador'),
('contador', '$2y$10$izarKMiJmTAk9grMNLhb2eOdxKhIHloqE2kXFUEP9Vye72K9nizxW', 'Contador General', 'contador');

-- ------------------------------------------------------------
-- Tabla: empresa
-- ------------------------------------------------------------
CREATE TABLE empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    nit VARCHAR(50) NOT NULL,
    direccion VARCHAR(200) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO empresa (nombre, nit, direccion, telefono) VALUES
('Mi Empresa S.A.', '0000000-0', 'Av. Principal #123', '+591 70000000');

-- ------------------------------------------------------------
-- Tabla: cuentas (Plan de Cuentas)
-- Restricción: codigo SOLO numérico de exactamente 8 dígitos, UNIQUE
-- ------------------------------------------------------------
CREATE TABLE cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo CHAR(8) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('activo','pasivo','patrimonio','ingreso','gasto') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT chk_codigo_numerico CHECK (codigo REGEXP '^[0-9]{8}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plan de cuentas inicial (15 cuentas básicas)
INSERT INTO cuentas (codigo, nombre, tipo) VALUES
-- ACTIVOS
('11010001', 'Caja General',                    'activo'),
('11020001', 'Bancos Moneda Nacional',          'activo'),
('11030001', 'Cuentas por Cobrar Clientes',     'activo'),
('11040001', 'Inventario de Mercaderías',       'activo'),
('12010001', 'Mobiliario y Equipo',             'activo'),
-- PASIVOS
('21010001', 'Cuentas por Pagar Proveedores',   'pasivo'),
('21020001', 'Impuestos por Pagar',             'pasivo'),
('22010001', 'Préstamos Bancarios L/P',         'pasivo'),
-- PATRIMONIO
('31010001', 'Capital Social',                  'patrimonio'),
('32010001', 'Utilidades Retenidas',            'patrimonio'),
-- INGRESOS
('41010001', 'Ingresos por Ventas',             'ingreso'),
('41020001', 'Ingresos por Servicios',          'ingreso'),
-- GASTOS
('51010001', 'Gastos de Sueldos y Salarios',    'gasto'),
('51020001', 'Gastos de Alquiler',              'gasto'),
('51030001', 'Gastos de Servicios Básicos',     'gasto');

-- ------------------------------------------------------------
-- Tabla: comprobantes (cabecera de asientos)
-- ------------------------------------------------------------
CREATE TABLE comprobantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    fecha DATE NOT NULL,
    descripcion VARCHAR(300) NOT NULL,
    usuario_id INT DEFAULT NULL,
    total_debe DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_haber DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comprobante_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabla: comprobante_detalles (líneas de los asientos)
-- ------------------------------------------------------------
CREATE TABLE comprobante_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comprobante_id INT NOT NULL,
    cuenta_id INT NOT NULL,
    debe DECIMAL(14,2) NOT NULL DEFAULT 0,
    haber DECIMAL(14,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_detalle_comprobante
        FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_cuenta
        FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Datos de ejemplo: un comprobante inicial (opcional)
-- ------------------------------------------------------------
INSERT INTO comprobantes (numero, fecha, descripcion, usuario_id, total_debe, total_haber) VALUES
('CMP-0001', CURDATE(), 'Apertura de caja con aporte de capital', 1, 5000.00, 5000.00);

INSERT INTO comprobante_detalles (comprobante_id, cuenta_id, debe, haber) VALUES
(1, (SELECT id FROM cuentas WHERE codigo = '11010001'), 5000.00, 0.00),
(1, (SELECT id FROM cuentas WHERE codigo = '31010001'), 0.00, 5000.00);
