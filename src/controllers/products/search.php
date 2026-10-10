<?php
/**
 * Búsqueda de productos.
 *
 * PHP hace la consulta a la base y responde el resultado directamente como JSON
 * (no se guarda ningún archivo en el disco). El JavaScript lo recibe y dibuja las cards.
 *
 * GET params: q (texto), categoria (nombre de la categoría)
 */
require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Cualquier warning/notice que PHP imprima rompería el JSON: lo capturamos y lo descartamos
ob_start();
function responder(array $data, int $status = 200): void {
    ob_end_clean();
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user'])) {
    responder(['success' => false, 'error' => 'No autorizado'], 401);
}

$q         = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

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
        // Plural -> singular simple (el sufijo es ASCII, por eso alcanza con substr)
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

responder([
    'success'   => true,
    'query'     => $q,
    'categoria' => $categoria,
    'total'     => count($productos),
    'products'  => $productos,
]);