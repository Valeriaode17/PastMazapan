<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) { echo json_encode([]); exit; }

$tipo = $_GET['tipo'] ?? ''; // 'venta', 'compra', 'devolucion'
$id   = intval($_GET['id']);

$data = [];

if ($tipo === 'venta') {
    // Detalles de Venta
    $sql = "SELECT i.nombre, d.cantidad, d.precio_unitario, d.total 
            FROM ventas_det d 
            JOIN items i ON d.id_item = i.id 
            WHERE d.id_venta = $id";
            
} elseif ($tipo === 'compra') {
    // Detalles de Compra
    $sql = "SELECT i.nombre, d.cantidad, d.precio_unitario, d.total 
            FROM compras_det d 
            JOIN items i ON d.id_item = i.id 
            WHERE d.id_compra = $id";

} elseif ($tipo === 'devolucion') {
    // Detalles de Devolución
    $sql = "SELECT i.nombre, d.cantidad, 0 as precio_unitario, 0 as total 
            FROM devoluciones_det d 
            JOIN items i ON d.id_item = i.id 
            WHERE d.id_devolucion = $id";
} else {
    echo json_encode([]); exit;
}

$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>