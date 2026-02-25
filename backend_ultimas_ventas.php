<?php

session_start();
require 'db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'No logueado']);
    exit();
}


$sql = "SELECT v.id, v.total, v.fecha, u.nombre as cajero 
        FROM ventas v
        JOIN usuarios u ON v.id_usuario = u.id
        ORDER BY v.id DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

$ventas = [];
while ($row = $res->fetch_assoc()) {
    $ventas[] = $row;
}


echo json_encode(['success' => true, 'data' => $ventas]);
?>