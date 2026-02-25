<?php
require 'db.php';
header('Content-Type: application/json');

if (!isset($_GET['folio'])) { 
    echo json_encode([]); 
    exit; 
}

$folio = intval($_GET['folio']);

$sql = "SELECT d.id_venta, d.id_item, i.nombre, d.cantidad, d.precio_unitario, d.total, d.devuelto 
        FROM ventas_det d
        JOIN items i ON d.id_item = i.id
        WHERE d.id_venta = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $folio);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>