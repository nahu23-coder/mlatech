<?php
require_once __DIR__ . '/../../config/bootstrap.php';

// Paso clave #1: Validar tipo de solicitud ----------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  // Si la solicitud no es POST, volves al register
  header('Location: /src/views/auth/register.php');
  exit;
}

// Paso clave #2: Tomar datos -----------------------------------
$data = [
  'email'           => trim($_POST['email'] ?? ''), // trim(str) saca los espacios al inicio y al final
  'name'            => trim($_POST['name'] ?? ''),
  'password'        => $_POST['password'] ?? '',
  'repeatPassword'  => $_POST['repeatPassword'] ?? ''
];

$errores = [];

if ($data['name'] === '') {
    $errores[] = 'El nombre es obligatorio.';
}

if ($data['email'] === '') {
    $errores[] = 'El email es obligatorio.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no tiene un formato válido.';
}

if ($data['password'] === '') {
    $errores[] = 'La contraseña es obligatoria.';
} elseif (strlen($data['password']) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
}

if ($data['repeatPassword'] === '') {
    $errores[] = 'Debes repetir la contraseña.';
} elseif ($data['password'] !== $data['repeatPassword']) {
    $errores[] = 'Las contraseñas no coinciden.';
}

// Si hay errores, volvemos al registro
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header('Location: /src/views/auth/register.php');
    exit;
}

// Validaciones básicas
// ...

// Paso clave #3: Hacer cosas ----------------------------------
try {
  // Validamos que el usuario no exista
  // ...


  // Hasheamos la contraseña, nunca se guarda en texto plano
  $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

  // Insertamos en DB
  $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
  $stmt->execute([
    'name'     => $data['name'],
    'email'    => $data['email'],
    'password' => $hashedPassword,
  ]);

  // Cargamos $_SESSION['user'], para poder pasar al index
  $_SESSION['user'] = [
    'id'    => $pdo->lastInsertId(),
    'name'  => $data['name'],
    'email' => $data['email'],
  ];

  // Pateado para el index
  header('Location: /src/views/index.php');
  exit;
} catch (PDOException $e) {
  exit;
}
