<?php

session_start();
require 'db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }

$tipo = $_GET['tipo'] ?? ''; 
$id   = intval($_GET['id']);

$data = [];

if ($tipo === 'venta') {
   
    $sql = "SELECT IFNULL(i.nombre, 'Producto Eliminado') as nombre, 
                   d.cantidad, d.precio_unitario, d.total 
            FROM ventas_det d 
            LEFT JOIN items i ON d.id_item = i.id 
            WHERE d.id_venta = $id";
            
} elseif ($tipo === 'compra') {

    $sql = "SELECT IFNULL(i.nombre, 'Producto Eliminado') as nombre, 
                   d.cantidad, d.precio_unitario, d.total 
            FROM compras_det d 
            LEFT JOIN items i ON d.id_item = i.id 
            WHERE d.id_compra = $id";

} elseif ($tipo === 'devolucion') {
   
    $sql = "SELECT IFNULL(i.nombre, 'Producto Eliminado') as nombre, 
                   d.cantidad, 0 as precio_unitario, 0 as total 
            FROM devoluciones_det d 
            LEFT JOIN items i ON d.id_item = i.id 
            WHERE d.id_devolucion = $id";
} else {
    echo json_encode([]); 
    exit;
}

$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>