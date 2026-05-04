# Sistema de Contabilidad Básico

Sistema web de contabilidad nivel académico (universitario) desarrollado en
**PHP puro (sin frameworks) + MariaDB / MySQL**.

Pensado para correr directamente sobre **XAMPP** o cualquier servidor local con
PHP y MariaDB. No requiere instaladores ni asistentes de configuración.

---

## Características

- Autenticación con sesiones PHP y dos roles: **administrador** y **contador**.
- Dashboard simple con accesos a todos los módulos.
- Gestión de **usuarios** (solo administrador): crear, editar, eliminar y
  asignar roles.
- **Datos de la empresa** (solo administrador): nombre, NIT, dirección y
  teléfono. Estos datos aparecen en todos los reportes.
- **Plan de cuentas** (CRUD completo) con validaciones obligatorias:
  - El código debe ser **solo numérico**, **exactamente 8 dígitos**.
  - Validación tanto en **frontend (JavaScript)** como en **backend (PHP)**.
  - Restricción `UNIQUE` en la base de datos para evitar duplicados.
  - 15 cuentas iniciales precargadas (Activos, Pasivos, Patrimonio, Ingresos,
    Gastos).
- **Comprobantes / asientos contables**: fecha, descripción y detalle de
  cuentas con **debe** y **haber**, con validación obligatoria de
  `suma del debe = suma del haber`.
- **Libro diario**: listado cronológico de comprobantes con filtros por fecha.
- **Libro mayor**: movimientos por cuenta con filtros por fecha y cálculo de
  saldo acumulado.
- **Balance de comprobación**: sumas y saldos por cuenta.
- **Balance general**: clasificación simple en Activo / Pasivo / Patrimonio
  (incluyendo el resultado del ejercicio).
- **Impresión** en todos los reportes:
  - Vista imprimible mediante `window.print()` con CSS específico para
    impresión (`@media print`).
  - Encabezado con datos de la empresa, título del reporte y fecha de
    impresión.
- Conexión segura mediante `mysqli` con consultas preparadas (protección
  básica contra SQL Injection).

---

## Instalación (5 pasos)

1. **Clonar / copiar el proyecto** dentro de la carpeta `htdocs` de XAMPP:

   ```
   C:\xampp\htdocs\sis_prueba_contable2\
   ```

2. **Iniciar Apache y MySQL** desde el panel de control de XAMPP.

3. **Importar la base de datos** abriendo
   [phpMyAdmin](http://localhost/phpmyadmin) y ejecutando el archivo:

   ```
   database/conta_prueba2.sql
   ```

   Esto crea la base `conta_prueba2`, todas las tablas, los usuarios por
   defecto y las 15 cuentas iniciales.

4. (Opcional) Ajustar las credenciales de la base de datos en
   `config/db.php` si su MariaDB no usa los valores por defecto de XAMPP
   (usuario `root` sin contraseña).

5. **Abrir el sistema** en el navegador:

   ```
   http://localhost/sis_prueba_contable2/
   ```

   e iniciar sesión con cualquiera de los usuarios precargados.

---

## Usuarios precargados

| Usuario    | Contraseña | Rol            |
|------------|------------|----------------|
| `admin`    | `admin123` | administrador  |
| `contador` | `admin123` | contador       |

> ⚠️ Cambia las contraseñas desde el módulo **Usuarios** después del primer
> ingreso.

---

## Estructura del proyecto

```
sis_prueba_contable2/
├── assets/
│   ├── css/
│   │   ├── style.css       Estilos generales
│   │   └── print.css       Estilos para impresión
│   └── js/
│       └── main.js         Validaciones de cliente
├── config/
│   ├── config.php          Configuración general (BASE_URL, sesión, ...)
│   └── db.php              Conexión a MariaDB
├── database/
│   └── conta_prueba2.sql    Script de creación de BD + datos iniciales
├── includes/
│   ├── auth.php            Control de sesión y roles
│   ├── functions.php       Helpers (e, money, validarCodigoCuenta, ...)
│   ├── header.php          Cabecera común (menú, alertas)
│   ├── footer.php          Pie común
│   ├── header_print.php    Cabecera de reportes imprimibles
│   └── footer_print.php    Pie de reportes imprimibles
├── modules/
│   ├── usuarios/           CRUD de usuarios (solo admin)
│   ├── empresa/            Datos de la empresa (solo admin)
│   ├── cuentas/            Plan de cuentas (CRUD + impresión)
│   ├── comprobantes/       Asientos contables (CRUD + ver + impresión)
│   ├── libro_diario/       Libro diario + impresión
│   ├── libro_mayor/        Libro mayor por cuenta + impresión
│   ├── balance_comprobacion/  Balance de comprobación + impresión
│   └── balance_general/    Balance general + impresión
├── dashboard.php
├── index.php               Redirige a login o dashboard
├── login.php
├── logout.php
└── README.md
```

---

## Validaciones del código de cuenta

| Caso                          | Resultado                          |
|-------------------------------|------------------------------------|
| `12345678`                    | ✔️ Válido (8 dígitos)              |
| `1234567` / `123456789`       | ❌ Rechazado (longitud incorrecta) |
| `1234ABCD`                    | ❌ Rechazado (contiene letras)     |
| `12345678` repetido           | ❌ Rechazado (UNIQUE en BD)        |

La validación se aplica en tres capas:

1. **Frontend (JavaScript)**: el campo solo permite dígitos y se trunca a 8.
2. **Backend (PHP)**: `validarCodigoCuenta()` con regex `^[0-9]{8}$`.
3. **Base de datos**: `UNIQUE` + `CHECK (codigo REGEXP '^[0-9]{8}$')`.

---

## Validación del comprobante (debe = haber)

- En el formulario, un script recalcula los totales en tiempo real y muestra
  un aviso visual indicando si el asiento está balanceado.
- Si el usuario intenta enviar un comprobante donde
  `total_debe ≠ total_haber` o ambos son cero, el envío se cancela tanto en
  frontend como en backend, y se muestra un mensaje de error.
- Toda la operación de guardado se ejecuta dentro de una transacción
  (`BEGIN / COMMIT / ROLLBACK`) para mantener consistencia.

---

## Notas técnicas

- Todas las consultas usan **declaraciones preparadas** (`mysqli prepare /
  bind_param`) para protegerse contra inyección SQL.
- Las contraseñas se almacenan con `password_hash()` (bcrypt) y se verifican
  con `password_verify()`.
- Los reportes incluyen un encabezado uniforme con los datos de la empresa,
  el título del reporte y la fecha de impresión.
- La hoja de estilos `print.css` oculta los elementos con clase `.no-print`
  durante la impresión y aplica un formato sobrio tipo documento contable.

---

## Licencia / uso

Proyecto académico de uso libre con fines educativos.
