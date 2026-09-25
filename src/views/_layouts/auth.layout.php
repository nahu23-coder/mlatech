<?php
require_once __DIR__ . '/../../config/bootstrap.php'; # Acá linkea las configuraciones de bootstrap.php
if (isset($_SESSION['user'])) {
  header('Location: /src/views/index.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href=<?= '/assets/css/bootstrap.min.css' ?> >
  <link rel="stylesheet" href=<?= '/assets/css/theme.css' ?> >
  <script src=<?= '/assets/js/bootstrap.min.js' ?>></script>
  <title>MLA Tech · Autenticación</title>
</head>
<body>

  <div class="auth-wrapper d-flex align-items-center justify-content-center">
    <div class="card auth-card shadow-sm m-3" style="width: 100%; max-width: 400px;">
      <div class="card-body p-4">
  
