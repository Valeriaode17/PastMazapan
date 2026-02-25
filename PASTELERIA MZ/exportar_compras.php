<?php
require 'db.php';

$filename = "reporte_compras_" . date('Ymd_Hi') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); 

fputcsv($out, ['Folio Compra', 'Fecha', 'Proveedor', 'Total Invertido']);

$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';
$condicion = "";

if (!empty($f_ini) && !empty($f_fin)) {
    $condicion = " WHERE c.fecha BETWEEN '$f_ini 00:00:00' AND '$f_fin 23:59:59' ";
}

$sql = "SELECT c.id, c.fecha, p.nombre as proveedor, c.total 
        FROM compras c 
        JOIN proveedores p ON c.id_proveedor = p.id 
        $condicion 
        ORDER BY c.id DESC";

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        date('Y-m-d H:i', strtotime($row['fecha'])),
        $row['proveedor'],
        number_format($row['total'], 2, '.', '')
    ]);
}

fclose($out);
?>