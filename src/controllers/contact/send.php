<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/views/contact.php');
    exit;
}

$data = [
    'name'    => trim($_POST['name'] ?? ''),
    'email'   => trim($_POST['email'] ?? ''),
    'subject' => trim($_POST['subject'] ?? ''),
    'message' => trim($_POST['message'] ?? ''),
];

if ($data['name'] === '' || $data['email'] === '' || $data['message'] === '') {
    header('Location: /src/views/contact.php');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)"
);
$stmt->execute($data);

header('Location: /src/views/contact.php?ok=1');
exit;
