<?php
require 'db.php';

// 1. Configurar nombre de archivo: reporte_inventario_YYYYMMDD_HHMM.csv
$filename = "reporte_inventario_" . date('Ymd_Hi') . ".csv";

// 2. Encabezados para forzar descarga y UTF-8
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 3. Abrir salida y agregar BOM (Byte Order Mark) para que Excel lea ñ y acentos
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); 

// 4. Encabezados de columnas
fputcsv($out, ['Codigo', 'Producto', 'Descripcion', 'Precio Unitario', 'Existencia', 'Valor Stock', 'Estado']);

// 5. Lógica de Filtro (Igual que en tu pantalla admin)
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'activos';
$condicion = ($filtro === 'activos') ? " AND i.activo = 1 " : "";

$sql = "SELECT i.codigo, i.nombre, i.descripcion, i.precio, IFNULL(e.cantidad, 0) as stock, i.activo 
        FROM items i 
        LEFT JOIN existencias e ON i.id = e.id_item 
        WHERE 1=1 $condicion 
        ORDER BY i.nombre ASC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['codigo'],
        $row['nombre'],
        $row['descripcion'],
        number_format($row['precio'], 2, '.', ''), // Formato monetario: 1234.50
        $row['stock'],
        number_format($row['precio'] * $row['stock'], 2, '.', ''), // Valor total
        ($row['activo'] == 1 ? 'Activo' : 'Inactivo')
    ]);
}

fclose($out);
?>