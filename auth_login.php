<?php

session_start();
require 'db.php';

if (!isset($_POST['correo']) || !isset($_POST['password'])) {
    header("Location: index.php?error=Por favor llena el formulario");
    exit();
}

$correo = $_POST['correo'];
$password = $_POST['password']; 

$sql = "SELECT id, nombre, contrasena, rol, activo FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    
    
    if (password_verify($password, $row['contrasena'])) { 
    
        if($row['activo'] == 0){
            header("Location: index.php?error=Usuario inactivo");
            exit();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['nombre'];
        $_SESSION['user_role'] = $row['rol'];

        if ($row['rol'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: pos.php");
        }
        exit();

    } else {
        header("Location: index.php?error=Contraseña incorrecta");
        exit();
    }
} else {
    header("Location: index.php?error=Usuario no encontrado");
    exit();
}
?>