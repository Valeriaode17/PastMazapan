<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { 
    die("Acceso denegado"); 
}

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = $_POST['password']; 
$rol = $_POST['rol'];
$activo = 1;


$stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmtCheck->bind_param("s", $correo);
$stmtCheck->execute();
$stmtCheck->store_result();

if($stmtCheck->num_rows > 0) {
    header("Location: admin_usuarios.php?error=El correo ya existe");
    exit();
}
$stmtCheck->close();


$pass_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, correo, contrasena, rol, activo) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ssssi", $nombre, $correo, $pass_hash, $rol, $activo);

if ($stmt->execute()) {
    header("Location: admin_usuarios.php?success=Usuario creado correctamente");
} else {
    header("Location: admin_usuarios.php?error=Error en base de datos");
}
?>