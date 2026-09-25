<?php
// Paso clave #1: Validar tipo de solicitud ----------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  // Si la solicitud no es POST, volves al login
  header('Location: /src/views/auth/login.php');
  exit;
}

// Paso clave #2: Tomar datos -----------------------------------
$data = [
  'email'           => trim($_POST['email'] ?? ''), // trim(str) saca los espacios al inicio y al final
  'password'        => $_POST['password'] ?? ''
];

try{
  // Paso #5: Conexion a la DB
   $conexion = new PDO(
        "mysql:host=localhost;dbname=mlatech;charset=utf8",
        "root",
        ""
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $a){
  echo "Error en la conexion con la DB";
}

try{
  // Paso #4: Verificar si existe el usuario -------------------
   $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':email' => $data['email']
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "El usuario no existe.";
        exit;
    }


  // Paso #5: Verificar si la contraseña es correcta -------------------
    
    if(!password_verify($data['password'], $usuario['password'])){
      echo "La contraseña es incorrecta.";
        exit;
    }

  // Paso #6: Dar acceso --------------------------
  session_start();

   $_SESSION['user'] = $usuario['id'];

    header('Location: /src/views/index.php');
    exit;

} catch (Exception $e) {

    echo "Ocurrió un error: " . $e->getMessage();

}
?>
