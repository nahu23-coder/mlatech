<?php
/**
 * Búsqueda de productos.
 *
 * 1) PHP hace la consulta a la base (con prepared statements).
 * 2) PHP guarda el resultado en un archivo .json (uno por sesión, así dos
 *    usuarios buscando a la vez no se pisan el archivo).
 * 3) Responde con la ruta del .json; el JavaScript lo lee y dibuja las cards.
 *
 * GET params: q (texto), categoria (nombre de la categoría)
 */
require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Cualquier warning/notice que PHP imprima rompería el JSON: lo capturamos y lo descartamos
ob_start();
function responder(array $data, int $status = 200): void {
    ob_end_clean();
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user'])) {
    responder(['success' => false, 'error' => 'No autorizado'], 401);
}

$q         = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

// ---------- 1) Query ----------
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image_url,
               c.name AS category_name, c.icon AS category_icon
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE 1 = 1";
$params = [];

if ($q !== '') {
    // Cada palabra tiene que aparecer en el nombre, la descripción o la categoría (AND entre palabras).
    // Así "procesadores", "placa video" o "ryzen 5" encuentran lo esperado.
    $palabras = array_slice(preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY), 0, 6);

    foreach ($palabras as $i => $palabra) {
        // Plural -> singular simple (el sufijo es ASCII, por eso alcanza con substr) ("procesadores" -> "procesador", "placas" -> "placa")
        $len = strlen($palabra);
        if ($len > 4 && substr($palabra, -2) === 'es') {
            $palabra = substr($palabra, 0, -2);
        } elseif ($len > 3 && substr($palabra, -1) === 's') {
            $palabra = substr($palabra, 0, -1);
        }

        // Escapamos % y _ para que el usuario no pueda usarlos como comodines
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $palabra) . '%';

        // Con EMULATE_PREPARES = false no se puede repetir el mismo :nombre, por eso uno por columna
        $sql .= " AND (p.name LIKE :w{$i}a OR p.description LIKE :w{$i}b OR c.name LIKE :w{$i}c)";
        $params[":w{$i}a"] = $like;
        $params[":w{$i}b"] = $like;
        $params[":w{$i}c"] = $like;
    }
}

if ($categoria !== '') {
    $sql .= " AND c.name = :categoria";
    $params[':categoria'] = $categoria;
}

$sql .= " ORDER BY c.name, p.name";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    responder(['success' => false, 'error' => 'Error en la consulta: ' . $e->getMessage()], 500);
}

// PDO devuelve DECIMAL/INT como string; los pasamos a número para el JSON
foreach ($productos as &$p) {
    $p['price'] = (float) $p['price'];
    $p['stock'] = (int) $p['stock'];
}
unset($p);

// ---------- 2) Escribimos el .json ----------
$dir = __DIR__ . '/../../../storage/search';
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    responder(['success' => false, 'error' => 'No se pudo crear la carpeta ' . $dir . ' (revisá los permisos)'], 500);
}

$fileName = 'search_' . hash('sha256', session_id()) . '.json';
$filePath = $dir . '/' . $fileName;

$payload = [
    'query'        => $q,
    'categoria'    => $categoria,
    'generated_at' => date('c'),
    'total'        => count($productos),
    'products'     => $productos,
];

// Escribimos a un temporal y renombramos: el navegador nunca lee un JSON a medio escribir
$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$tmp  = $filePath . '.' . uniqid('', true) . '.tmp';
$ok   = @file_put_contents($tmp, $json, LOCK_EX) !== false && @rename($tmp, $filePath);

if (!$ok) {
    @unlink($tmp);
    // Plan B (en Windows rename puede fallar si el archivo está abierto): escribir directo
    $ok = @file_put_contents($filePath, $json, LOCK_EX) !== false;
}

if (!$ok) {
    responder(['success' => false, 'error' => 'No se pudo escribir ' . $filePath . ' (revisá los permisos)'], 500);
}

// ---------- 3) Le decimos al JS dónde está el archivo ----------
responder([
    'success' => true,
    'file'    => '/storage/search/' . $fileName,
    'total'   => count($productos),
]);