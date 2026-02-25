<?php
require 'db.php';

$filename = "reporte_ventas_" . date('Ymd_Hi') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM

fputcsv($out, ['Folio Venta', 'Fecha', 'Cajero', 'Codigo Externo', 'Total Venta']);

// Filtros de fecha si existen
$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';
$condicion = "";

if (!empty($f_ini) && !empty($f_fin)) {
    $condicion = " WHERE v.fecha BETWEEN '$f_ini 00:00:00' AND '$f_fin 23:59:59' ";
}

$sql = "SELECT v.id, v.fecha, u.nombre as cajero, v.codigo_externo, v.total 
        FROM ventas v 
        JOIN usuarios u ON v.id_usuario = u.id 
        $condicion 
        ORDER BY v.id DESC";

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($out, [
        $row['id'],
        date('Y-m-d H:i', strtotime($row['fecha'])), // Formato fecha: AAAA-MM-DD HH:MM
        $row['cajero'],
        $row['codigo_externo'],
        number_format($row['total'], 2, '.', '') // Sin signo $
    ]);
}

fclose($out);
?>