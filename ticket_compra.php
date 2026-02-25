<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { die("Acceso denegado."); }
if (!isset($_GET['id'])) die("ID no especificado");
$id = intval($_GET['id']);

$sqlHead = "SELECT c.*, p.nombre as proveedor FROM compras c JOIN proveedores p ON c.id_proveedor = p.id WHERE c.id = $id";
$resHead = $conn->query($sqlHead);
if ($resHead->num_rows === 0) die("Compra no encontrada");
$compra = $resHead->fetch_assoc();

$sqlDet = "SELECT cd.*, i.nombre FROM compras_det cd JOIN items i ON cd.id_item = i.id WHERE cd.id_compra = $id";
$detalles = $conn->query($sqlDet);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entrada #<?= $id ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'Courier New', monospace; width: 76mm; margin: 2mm auto; font-size: 12px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed black; margin: 5px 0; width: 100%; }
        .logo { max-width: 150px; display: block; margin: 0 auto 5px auto; filter: grayscale(100%); }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center">
        <img src="img_items/logo.PNG" alt="Logo" class="logo" onerror="this.style.display='none'">
        
        <div class="bold" style="font-size: 14px;">ENTRADA DE ALMACÉN</div>
        <div>Pastelería MAZAPAN</div>
        <br>
        <div>Prov: <strong><?= $compra['proveedor'] ?></strong></div>
        <div>Fecha: <?= date("d/m/Y H:i", strtotime($compra['fecha'])) ?></div>
        <div>Folio: #<?= str_pad($compra['id'], 6, "0", STR_PAD_LEFT) ?></div>
    </div>

    <div class="divider"></div>

    <table>
        <tr class="bold">
            <td>Cant</td><td>Producto</td><td class="text-right">Total</td>
        </tr>
        <?php while($d = $detalles->fetch_assoc()): ?>
        <tr>
            <td style="text-align:center;"><?= $d['cantidad'] ?></td>
            <td><?= substr($d['nombre'],0,18) ?></td>
            <td class="text-right">$<?= number_format($d['total'],2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="divider"></div>

    <div class="text-right">
        <span>TOTAL GASTO:</span><br>
        <strong style="font-size:16px;">$<?= number_format($compra['total'], 2) ?></strong>
    </div>

    <br><br>
    <div class="text-center">__________________________<br>Firma de Recibido</div>
    <button class="no-print" onclick="window.close()" style="width:100%; margin-top:20px; padding:10px;">Cerrar</button>
</body>
</html>