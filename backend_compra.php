<?php

session_start();
require 'db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { 
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado']); 
    exit; 
}


$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'msg' => 'Carrito vacío o error de datos']);
    exit;
}

$conn->begin_transaction();

try {
    
    $total_compra = 0;
    foreach($data['items'] as $item) {
        $total_compra += ($item['costo'] * $item['cantidad']);
    }

    
    $stmt = $conn->prepare("INSERT INTO compras (id_proveedor, total, fecha) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $data['id_proveedor'], $total_compra);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar cabecera: " . $stmt->error);
    }
    $id_compra = $conn->insert_id;

   
    $stmt_det = $conn->prepare("INSERT INTO compras_det (id_compra, id_item, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
    
   
    $stmt_upd = $conn->prepare("UPDATE existencias SET cantidad = cantidad + ? WHERE id_item = ?");
    
    $stmt_ins = $conn->prepare("INSERT INTO existencias (id_item, cantidad) VALUES (?, ?)");

    foreach($data['items'] as $item) {
        $id_item  = $item['id_item'];
        $cantidad = $item['cantidad'];
        $costo    = $item['costo'];
        $subtotal = $costo * $cantidad;

        
        $stmt_det->bind_param("iiidd", $id_compra, $id_item, $cantidad, $costo, $subtotal);
        if (!$stmt_det->execute()) {
            throw new Exception("Error al guardar detalle del item " . $id_item);
        }

       
        $stmt_upd->bind_param("di", $cantidad, $id_item);
        $stmt_upd->execute();

     
        if ($stmt_upd->affected_rows === 0) {
            $stmt_ins->bind_param("id", $id_item, $cantidad);
            $stmt_ins->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'msg' => 'Compra registrada y stock aumentado']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
?>