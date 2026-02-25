<?php
require 'db.php';

$filename = "reporte_ventas_detalle_" . date('Ymd_Hi') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); 

fputcsv($out, ['Folio Venta', 'Fecha', 'Codigo Producto', 'Producto', 'Cantidad', 'Precio Unitario', 'Subtotal']);

$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';
$condicion = "";

if (!empty($f_ini) && !empty($f_fin)) {
    $condicion = " WHERE v.fecha BETWEEN '$f_ini 00:00:00' AND '$f_fin 23:59:59' ";
}

$sql = "SELECT v.id as folio, v.fecha, i.codigo, i.nombre, d.cantidad, d.precio_unitario, d.total
        FROM ventas_det d
        JOIN ventas v ON d.id_venta = v.id
        JOIN items i ON d.id_item = i.id
        $condicion
        ORDER BY v.id DESC";

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['folio'],
        date('Y-m-d H:i', strtotime($row['fecha'])),
        $row['codigo'],
        $row['nombre'],
        $row['cantidad'],
        number_format($row['precio_unitario'], 2, '.', ''),
        number_format($row['total'], 2, '.', '')
    ]);
}

fclose($out);
?>