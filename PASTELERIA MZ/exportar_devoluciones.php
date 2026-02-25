<?php
require 'db.php';

$filename = "reporte_devoluciones_" . date('Ymd_Hi') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); 

fputcsv($out, ['Folio Devolucion', 'Fecha', 'Ticket Origen', 'Codigo', 'Producto', 'Cantidad Devuelta', 'Motivo', 'Procesado Por']);

$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';
$condicion = "";

if (!empty($f_ini) && !empty($f_fin)) {
    $condicion = " WHERE d.fecha BETWEEN '$f_ini 00:00:00' AND '$f_fin 23:59:59' ";
}

$sql = "SELECT d.id, d.fecha, d.id_venta, i.codigo, i.nombre, dd.cantidad, d.motivo, u.nombre as usuario
        FROM devoluciones d
        JOIN devoluciones_det dd ON d.id = dd.id_devolucion
        JOIN items i ON dd.id_item = i.id
        JOIN usuarios u ON d.id_usuario = u.id
        $condicion
        ORDER BY d.id DESC";

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        date('Y-m-d H:i', strtotime($row['fecha'])),
        $row['id_venta'],
        $row['codigo'],
        $row['nombre'],
        $row['cantidad'],
        $row['motivo'],
        $row['usuario']
    ]);
}

fclose($out);
?>