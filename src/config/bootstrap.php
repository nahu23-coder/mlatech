<?php
/**
 * Bootstrap global del proyecto.
 *
 * Se incluye al principio de cada "entry point" (archivos a los que
 * se accede directamente por URL: index.php, controllers/*.php).
 * Las vistas que se incluyen con require/include (layout.php, login.php, etc.)
 * NO necesitan llamarlo: ya van a recibir la sesión abierta.
 */
 
// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');
 
// Iniciamos la sesión si todavía no existe una
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Conexión a la base de datos, disponible en todo el proyecto como $pdo
require_once __DIR__ . '/database.php';

// Resto de las variables de entorno (Mercado Pago, IA, URL del sitio),
// disponibles en todo el proyecto como constantes.
$__env = parse_ini_file(__DIR__ . '/../../.env');
define('MP_ACCESS_TOKEN', $__env['MP_ACCESS_TOKEN'] ?? '');
define('SITE_URL', rtrim($__env['SITE_URL'] ?? 'http://localhost', '/'));
define('AI_API_KEY', $__env['AI_API_KEY'] ?? '');
unset($__env);
 
