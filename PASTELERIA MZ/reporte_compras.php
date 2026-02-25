<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit; }

$fecha_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$fecha_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';

$condicion = "";
$texto_filtro = "";

if (!empty($fecha_ini) && !empty($fecha_fin)) {
    $condicion = " WHERE c.fecha BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ORDER BY c.id ASC";
    $texto_filtro = "Filtros aplicados: Fecha del " . date("d/m/Y", strtotime($fecha_ini)) . " al " . date("d/m/Y", strtotime($fecha_fin));
} else {
    $condicion = " ORDER BY c.id DESC LIMIT 100";
    $texto_filtro = "Filtros aplicados: Últimos 100 registros";
}

$sql = "SELECT c.*, p.nombre as proveedor FROM compras c JOIN proveedores p ON c.id_proveedor = p.id $condicion";
$compras = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><title>Reporte Compras</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; margin: 20px; font-size: 12px; }
    .header-wrapper { position: relative; min-height: 80px; border-bottom: 2px solid #444; padding-bottom: 15px; margin-bottom: 20px; }
    .logo { position: absolute; top: 0; left: 0; width: 28mm; }
    .header-content { text-align: center; width: 100%; padding-top: 5px; }
    .badge-filtro { background: #eee; padding: 5px 10px; font-weight: bold; border: 1px solid #ccc; display: inline-block; margin-top:5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f4f4f4; text-transform: uppercase; font-size: 11px; }
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
            <h2>Reporte de Compras por Rango</h2>
            <div>Fecha: <?= date("d/m/Y H:i") ?></div>
            <div class="badge-filtro"><?= $texto_filtro ?></div>
        </div>
    </div>

    <table>
        <thead><tr><th>Folio</th><th>Fecha</th><th>Proveedor</th><th style="text-align:right;">Inversión</th></tr></thead>
        <tbody>
            <?php $total=0; $c=0; while($r=$compras->fetch_assoc()): $total+=$r['total']; $c++; ?>
            <tr>
                <td>#<?= str_pad($r['id'],6,"0",STR_PAD_LEFT) ?></td>
                <td><?= $r['fecha'] ?></td>
                <td><?= $r['proveedor'] ?></td>
                <td style="text-align:right;">$<?= number_format($r['total'],2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr style="background:#ddd; font-weight:bold;"><td colspan="3" align="right">TOTAL:</td><td align="right">$<?= number_format($total,2) ?></td></tr>
        </tbody>
    </table>

    <div class="footer-report">
        <strong>Resumen:</strong> Total registros: <?= $c ?> | Suma total: $<?= number_format($total,2) ?>
        <div class="signature-box">
            Generado por: <strong><?= $_SESSION['user_name'] ?></strong>
            <br><br><br>
            <div class="linea-firma"></div>
            <div style="margin-top:5px;">Firma / Vo. Bo.</div>
        </div>
    </div>
</body>
</html>