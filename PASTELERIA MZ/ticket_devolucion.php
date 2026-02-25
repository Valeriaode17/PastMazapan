<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) die("Acceso denegado.");
if (!isset($_GET['id'])) die("ID no especificado");
$id = intval($_GET['id']);

$sqlHead = "SELECT d.*, u.nombre as usuario FROM devoluciones d JOIN usuarios u ON d.id_usuario = u.id WHERE d.id = $id";
$resHead = $conn->query($sqlHead);
if ($resHead->num_rows === 0) die("Devolución no encontrada");
$dev = $resHead->fetch_assoc();

$sqlDet = "SELECT dd.*, i.nombre FROM devoluciones_det dd JOIN items i ON dd.id_item = i.id WHERE dd.id_devolucion = $id";
$detalles = $conn->query($sqlDet);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devolución #<?= $id ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'Courier New', monospace; width: 76mm; margin: 2mm auto; font-size: 12px; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed black; margin: 5px 0; width: 100%; }
        .logo { max-width: 150px; display: block; margin: 0 auto 5px auto; filter: grayscale(100%); }
        table { width: 100%; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center">
        <img src="img_items/logo.PNG" alt="Logo" class="logo" onerror="this.style.display='none'">

        <div class="bold" style="font-size: 14px;">COMPROBANTE DE DEVOLUCIÓN</div>
        <div>Pastelería MAZAPAN</div>
        <br>
        <div>Folio Dev: <strong>#<?= str_pad($dev['id'], 6, "0", STR_PAD_LEFT) ?></strong></div>
        <div>Venta Orig: #<?= str_pad($dev['id_venta'], 6, "0", STR_PAD_LEFT) ?></div>
        <div>Fecha: <?= date("d/m/Y H:i", strtotime($dev['fecha'])) ?></div>
        <div>Atendió: <?= $dev['usuario'] ?></div>
    </div>

    <div class="divider"></div>

    <div class="text-center bold">PRODUCTOS REINGRESADOS</div>
    <br>

    <table>
        <tr class="bold"><td style="width:20%">CANT</td><td>PRODUCTO</td></tr>
        <?php while($d = $detalles->fetch_assoc()): ?>
        <tr>
            <td style="text-align:center; font-size:1.2em;"><?= $d['cantidad'] ?></td>
            <td><?= $d['nombre'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="divider"></div>
    <div class="text-center"><small>Mercancía reingresada al inventario.</small></div>
    <button class="no-print" onclick="window.close()" style="width:100%; margin-top:20px; padding:10px;">Cerrar</button>
</body>
</html>