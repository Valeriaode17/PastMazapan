<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    
    $stmt = $conn->prepare("UPDATE items SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?msg=eliminado");
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
} else {
    header("Location: admin.php");
}
?>