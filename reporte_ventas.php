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
    $condicion = " WHERE v.fecha BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ORDER BY v.id ASC";
    // TEXTO OBLIGATORIO DEL FILTRO
    $texto_filtro = "Filtros aplicados: Fecha del " . date("d/m/Y", strtotime($fecha_ini)) . " al " . date("d/m/Y", strtotime($fecha_fin));
} else {
    $condicion = " ORDER BY v.id DESC LIMIT 100";
    $texto_filtro = "Filtros aplicados: Últimos 100 registros (Sin rango de fecha)";
}

$sql = "SELECT v.*, u.nombre as cajero 
        FROM ventas v 
        JOIN usuarios u ON v.id_usuario = u.id 
        $condicion";
$ventas = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Ventas</title>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; font-size: 12px; color: #333; }
    
    /* LOGO Y ENCABEZADO */
    .header-wrapper { position: relative; min-height: 80px; border-bottom: 2px solid #444; padding-bottom: 15px; margin-bottom: 20px; }
    .logo { position: absolute; top: 0; left: 0; width: 28mm; } /* Logo 28mm Izq */
    .header-content { text-align: center; width: 100%; padding-top: 5px; }

    h2 { margin: 5px 0; text-transform: uppercase; letter-spacing: 1px; }
    .info { font-size: 11px; color: #666; margin-bottom: 5px; }
    
    /* ETIQUETA DE FILTROS */
    .badge-filtro { background: #eee; padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; font-weight: bold; display: inline-block; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; font-size: 11px; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .text-right { text-align: right; }

    /* PIE DE PÁGINA (FIRMAS) */
    .footer-report { margin-top: 50px; page-break-inside: avoid; }
    .signature-box { margin-top: 40px; text-align: center; }
    .linea-firma { border-top: 1px solid #000; width: 250px; margin: 0 auto; }
    

    @media print { .no-print { display: none; } }
    .btn { padding: 8px 15px; cursor: pointer; border: 1px solid #ccc; background: #eee; border-radius: 4px; }
</style>
</head>
<body onload="window.print()">

    <div class="no-print"  style="margin-bottom:15px;">
        <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn" onclick="window.close()">Cerrar</button>
    </div>

    <div class="header-wrapper">
        <img src="img_items/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="header-content">
            <h2>Reporte de Ventas por Rango</h2>
            <div class="info">Fecha emisión: <?= date("d/m/Y H:i") ?></div>
            <div class="badge-filtro"><?= $texto_filtro ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cajero</th>
                <th>Cód. Ref</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_gral = 0; $count = 0;
            while($r = $ventas->fetch_assoc()): 
                $total_gral += $r['total']; $count++;
            ?>
            <tr>
                <td>#<?= str_pad($r['id'], 6, "0", STR_PAD_LEFT) ?></td>
                <td><?= date("d/m/Y H:i", strtotime($r['fecha'])) ?></td>
                <td><?= $r['cajero'] ?></td>
                <td><?= $r['codigo_externo'] ?: '-' ?></td>
                <td class="text-right">$<?= number_format($r['total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr style="background:#e0e0e0; font-weight:bold;">
                <td colspan="4" class="text-right">TOTAL VENTAS:</td>
                <td class="text-right">$<?= number_format($total_gral, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-report">
        <strong>Resumen:</strong><br>
        Total registros: <?= $count ?><br>
        Suma total: $<?= number_format($total_gral, 2) ?><br><br>
        
        <div class="signature-box">
            Generado por: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
            <br><br><br>
            <div class="linea-firma"></div>
            <div style="margin-top:5px;">Firma / Vo. Bo.</div>
        </div>
    </div>
</body>
</html>