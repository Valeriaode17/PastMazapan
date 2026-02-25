<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role'])) { header("Location: index.php"); exit; }

$fecha_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$fecha_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';

$condicion = "";
$texto_filtro = "";

if (!empty($fecha_ini) && !empty($fecha_fin)) {
    $condicion = " WHERE d.fecha BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ORDER BY d.id ASC";
    $texto_filtro = "Filtros aplicados: Fecha del " . date("d/m/Y", strtotime($fecha_ini)) . " al " . date("d/m/Y", strtotime($fecha_fin));
} else {
    $condicion = " ORDER BY d.id DESC LIMIT 100";
    $texto_filtro = "Filtros aplicados: Últimos 100 registros";
}

// CONSULTA DETALLADA CON JOINS PARA OBTENER NOMBRES, PRECIOS Y MOTIVOS
$sql = "SELECT d.id, d.fecha, d.id_venta, d.motivo, 
               dd.cantidad, i.codigo, i.nombre, i.precio as precio_unitario
        FROM devoluciones d 
        JOIN devoluciones_det dd ON d.id = dd.id_devolucion
        JOIN items i ON dd.id_item = i.id
        $condicion";
$devs = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><title>Reporte Devoluciones</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; margin: 20px; font-size: 11px; }
    .header-wrapper { position: relative; min-height: 80px; border-bottom: 2px solid #444; padding-bottom: 15px; margin-bottom: 20px; }
    .logo { position: absolute; top: 0; left: 0; width: 28mm; }
    .header-content { text-align: center; width: 100%; padding-top: 5px; }
    .badge-filtro { background: #eee; padding: 5px 10px; font-weight: bold; border: 1px solid #ccc; display: inline-block; margin-top:5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 5px; text-align: center; }
    th { background: #f4f4f4; text-transform: uppercase; font-size: 10px; }
    .footer-report { margin-top: 50px; }
    .signature-box { margin-top: 40px; text-align: center; }
    .linea-firma { border-top: 1px solid #000; width: 250px; margin: 0 auto; }
    @media print { .no-print { display: none; } }
    .btn { padding: 8px 15px; cursor: pointer; background: #eee; border: 1px solid #ccc; }
</style>
</head>
<body onload="window.print()">
     <div class="no-print" style="margin-bottom:15px;">
        <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn" onclick="window.close()">Cerrar</button>
    </div>

    <div class="header-wrapper">
        <img src="img_items/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="header-content">
            <h2>Reporte de Devoluciones</h2>
            <div>Fecha: <?= date("d/m/Y H:i") ?></div>
            <div class="badge-filtro"><?= $texto_filtro ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Folio Venta</th>
                <th>Código</th>
                <th>Nombre Producto</th>
                <th>Cant. Devuelta</th>
                <th>Importe Ajustado</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sum_cant = 0;
            $sum_importe = 0;
            while($r=$devs->fetch_assoc()): 
                $importe = $r['cantidad'] * $r['precio_unitario'];
                $sum_cant += $r['cantidad'];
                $sum_importe += $importe;
            ?>
            <tr>
                <td><?= date("d/m/Y", strtotime($r['fecha'])) ?></td>
                <td>#<?= $r['id_venta'] ?></td>
                <td><?= $r['codigo'] ?></td>
                <td style="text-align:left;"><?= $r['nombre'] ?></td>
                <td><?= $r['cantidad'] ?></td>
                <td style="text-align:right;">$<?= number_format($importe, 2) ?></td>
                <td style="font-size:10px;"><?= $r['motivo'] ?></td>
            </tr>
            <?php endwhile; ?>
            <tr style="background:#e0e0e0; font-weight:bold;">
                <td colspan="4" style="text-align:right;">TOTALES:</td>
                <td><?= $sum_cant ?></td>
                <td style="text-align:right;">$<?= number_format($sum_importe, 2) ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-report">
        <strong>Resumen Final:</strong><br>
        Total Unidades Devueltas: <?= $sum_cant ?><br>
        Importe Total Devuelto: $<?= number_format($sum_importe, 2) ?>
        
        <div class="signature-box">
            Generado por: <strong><?= $_SESSION['user_name'] ?></strong>
            <br><br><br>
            <div class="linea-firma"></div>
            <div style="margin-top:5px;">Firma / Vo. Bo.</div>
        </div>
    </div>
</body>
</html>