<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_a_borrar = intval($_GET['id']);

    
    $stmt = $conn->prepare("SELECT rol, nombre FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_a_borrar);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $rol_objetivo = $row['rol'];

     
        if ($rol_objetivo === 'admin') {
            $countQuery = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin'");
            $countData = $countQuery->fetch_assoc();
            
            if ($countData['total'] <= 1) {
                
                header("Location: admin_usuarios.php?error=⚠️ IMPOSIBLE: No puedes borrar al último Administrador. Te quedarías sin acceso al sistema.");
                exit();
            }
        }

       
        $del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $del->bind_param("i", $id_a_borrar);

        if ($del->execute()) {
            header("Location: admin_usuarios.php?success=Usuario eliminado correctamente");
        } else {
            
            header("Location: admin_usuarios.php?error=No se puede borrar: Este usuario tiene ventas registradas en el historial.");
        }

    } else {
        header("Location: admin_usuarios.php?error=Usuario no encontrado");
    }

} else {
    header("Location: admin_usuarios.php");
}
?>