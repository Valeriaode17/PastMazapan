<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado");
}

$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];

$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
$precio = $_POST['precio'];
$cant   = $_POST['cantidad']; 


$nombre_imagen = 'default.png'; 

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $info = pathinfo($_FILES['imagen']['name']);
    $ext = $info['extension'];

    $nombre_imagen = "prod_" . date("YmdHis") . "." . $ext;
    move_uploaded_file($_FILES['imagen']['tmp_name'], "img_items/" . $nombre_imagen);
}


$stmt = $conn->prepare("INSERT INTO items (codigo, nombre, descripcion, precio, activo, imagen) VALUES (?, ?, ?, ?, 1, ?)");

$stmt->bind_param("sssds", $codigo, $nombre, $descripcion, $precio, $nombre_imagen);

if ($stmt->execute()) {
    $id_item = $stmt->insert_id;

    
    if ($cant > 0) {
        $stmt_stock = $conn->prepare("INSERT INTO existencias (id_item, cantidad) VALUES (?, ?)");
        $stmt_stock->bind_param("id", $id_item, $cant);
        $stmt_stock->execute();
    }
    
    header("Location: admin.php?success=Producto guardado");
} else {
    echo "Error BD: " . $conn->error;
}
?>