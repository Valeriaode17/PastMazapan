<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { 
    echo json_encode(['success'=>false, 'msg'=>'No autorizado']); exit; 
}

$data = json_decode(file_get_contents('php://input'), true);
$conn->begin_transaction();

try {
    $total = 0;
    foreach($data['items'] as $i) $total += ($i['costo'] * $i['cantidad']);

    // 1. Insertar Compra
    $stmt = $conn->prepare("INSERT INTO compras (id_proveedor, total, fecha) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $data['id_proveedor'], $total);
    $stmt->execute();
    $id_compra = $conn->insert_id;

    // 2. Detalles (EL TRIGGER AUTOMATICAMENTE SUMA AL STOCK)
    $stmt_det = $conn->prepare("INSERT INTO compras_det (id_compra, id_item, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
    
    foreach($data['items'] as $i) {
        $sub = $i['costo'] * $i['cantidad'];
        $stmt_det->bind_param("iiidd", $id_compra, $i['id'], $i['cantidad'], $i['costo'], $sub);
        $stmt_det->execute();
    }

    $conn->commit();
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false, 'msg'=>$e->getMessage()]);
}
?>