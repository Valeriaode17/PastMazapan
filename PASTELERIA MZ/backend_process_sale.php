<?php
session_start();
require 'db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) { 
    echo json_encode(['success'=>false, 'message'=>'Sesión expirada']); 
    exit; 
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) { 
    echo json_encode(['success'=>false, 'message'=>'Carrito vacío']); 
    exit; 
}


if (isset($_SESSION['ultima_venta_tiempo'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultima_venta_tiempo'];
    if ($tiempo_transcurrido < 5) {
        echo json_encode(['success'=>false, 'message'=>'⚠️ Procesando... espere un momento.']); 
        exit;
    }
}
$_SESSION['ultima_venta_tiempo'] = time();

$codigo_externo = isset($input['codigo_externo']) && $input['codigo_externo'] !== '' ? $input['codigo_externo'] : NULL;

$conn->begin_transaction();
try {
    $id_usuario = $_SESSION['user_id'];
    $total = 0;

    foreach($input['items'] as $i) { 
        $total += ($i['precio'] * $i['cantidad']); 
    }
    
    $subtotal = $total / 1.16;
    $iva = $total - $subtotal;

    $stmt = $conn->prepare("INSERT INTO ventas (id_usuario, subtotal, iva, total, codigo_externo, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iddds", $id_usuario, $subtotal, $iva, $total, $codigo_externo);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar venta: " . $stmt->error);
    }
    $id_venta = $conn->insert_id;

   
    $stmt_det = $conn->prepare("INSERT INTO ventas_det (id_venta, id_item, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
    
   

    foreach($input['items'] as $i) {
        $line_total = $i['precio'] * $i['cantidad'];
        
        
        $stmt_det->bind_param("iiidd", $id_venta, $i['id'], $i['cantidad'], $i['precio'], $line_total);
        if(!$stmt_det->execute()) {
            throw new Exception("Error al guardar detalle item ID: " . $i['id']);
        }

        
    }

    $conn->commit();
    echo json_encode(['success'=>true, 'message'=>'Venta exitosa', 'folio'=>$id_venta]);

} catch (Exception $e) {
    $conn->rollback();
    unset($_SESSION['ultima_venta_tiempo']); 
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
?>