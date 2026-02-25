<?php
// backend_devolucion.php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false, 'msg'=>'No autorizado']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$id_venta = $input['id_venta'];
$id_item  = $input['id_item'];
$cantidad = $input['cantidad'];
$motivo   = isset($input['motivo']) ? $input['motivo'] : 'Devolución Cliente';

$conn->begin_transaction();

try {
    // Verificar si ya se devolvió
    $check = $conn->prepare("SELECT devuelto FROM ventas_det WHERE id_venta = ? AND id_item = ?");
    $check->bind_param("ii", $id_venta, $id_item);
    $check->execute();
    $resCheck = $check->get_result();
    $fila = $resCheck->fetch_assoc();

    if (!$fila || $fila['devuelto'] == 1) {
        throw new Exception("Este producto ya fue procesado o no existe.");
    }

    // 1. Crear Devolución (Con Motivo)
    $stmtDev = $conn->prepare("INSERT INTO devoluciones (id_venta, id_usuario, motivo, fecha) VALUES (?, ?, ?, NOW())");
    $id_usuario = $_SESSION['user_id'];
    $stmtDev->bind_param("iis", $id_venta, $id_usuario, $motivo);
    $stmtDev->execute();
    $id_devolucion = $conn->insert_id;

    // 2. Crear Detalle (Actualiza Stock vía Trigger)
    $stmtDet = $conn->prepare("INSERT INTO devoluciones_det (id_devolucion, id_venta, id_item, cantidad, monto_devuelto) VALUES (?, ?, ?, ?, 0)"); 
    $stmtDet->bind_param("iiii", $id_devolucion, $id_venta, $id_item, $cantidad);
    $stmtDet->execute();

    // 3. Marcar venta como devuelta
    $stmtUpd = $conn->prepare("UPDATE ventas_det SET devuelto = 1 WHERE id_venta = ? AND id_item = ?");
    $stmtUpd->bind_param("ii", $id_venta, $id_item);
    $stmtUpd->execute();

    $conn->commit();
    echo json_encode(['success'=>true, 'msg'=>'Listo']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false, 'msg'=>$e->getMessage()]);
}
?>