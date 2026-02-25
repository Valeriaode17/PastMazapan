<?php
// ticket.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['folio'])) {
    die("Acceso denegado o folio no especificado.");
}

$folio = intval($_GET['folio']);

$sqlVenta = "SELECT v.*, u.nombre AS cajero
             FROM ventas v
             JOIN usuarios u ON v.id_usuario = u.id
             WHERE v.id = ?";
$stmt = $conn->prepare($sqlVenta);
$stmt->bind_param("i", $folio);
$stmt->execute();
$resVenta = $stmt->get_result();

if ($resVenta->num_rows === 0) die("Venta no encontrada");
$venta = $resVenta->fetch_assoc();

$sqlDetalles = "SELECT d.*, i.nombre, i.codigo 
                FROM ventas_det d
                JOIN items i ON d.id_item = i.id
                WHERE d.id_venta = ?";
$stmt2 = $conn->prepare($sqlDetalles);
$stmt2->bind_param("i", $folio);
$stmt2->execute();
$detalles = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $folio ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 76mm;
            margin: 2mm auto;
            background: white;
            color: black;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed black; margin: 5px 0; width: 100%; }
        
        .logo {
            max-width: 200px;
            display: block;
            margin: 0 auto 5px auto;
            filter: grayscale(100%);
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
      
        .col-cant { width: 10%; text-align: center; }
        .col-desc { width: 55%; text-align: left; }
        .col-precio { width: 15%; text-align: right; }
        .col-importe { width: 20%; text-align: right; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center">
        <img src="img_items/logo.PNG" alt="Logo" class="logo" onerror="this.style.display='none'">
        
        <div class="bold" style="font-size: 14px;">Pastelería MAZAPAN</div>
        <div>RFC: MAZA-850101-XXX</div>
        <div>Calle Insecto #123, Centro</div>
        <div>Mazatlán, Sinaloa</div>
        <div>Tel: (669) 987-6543</div>
    </div>

    <div class="divider"></div>

    <div>
        Folio: <strong>#<?= str_pad($venta['id'], 6, "0", STR_PAD_LEFT) ?></strong><br>
        Fecha: <?= date("Y-m-d H:i", strtotime($venta['fecha'])) ?><br>
        Cajero: <?= strtoupper($venta['cajero']) ?>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr class="bold">
                <td class="col-cant">Cnt</td>
                <td class="col-desc">Desc</td>
                <td class="col-precio">P.U.</td>
                <td class="col-importe">Imp.</td>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $detalles->fetch_assoc()): ?>
            <tr>
                <td class="col-cant"><?= $item['cantidad'] ?></td>
                <td class="col-desc">
                    <?= substr($item['nombre'], 0, 18) ?><br>
                    <small style="font-size:9px;">[<?= $item['codigo'] ?>]</small>
                </td>
                <td class="col-precio">$<?= number_format($item['precio_unitario'], 2) ?></td>
                <td class="col-importe">$<?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="font-weight: bold;">
        <tr>
            <td class="text-right">Subtotal:</td>
            <td class="text-right" style="width: 30%;">$<?= number_format($venta['subtotal'], 2) ?></td>
        </tr>
        <tr>
            <td class="text-right">IVA (16%):</td>
            <td class="text-right">$<?= number_format($venta['iva'], 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" style="font-size: 14px;">TOTAL:</td>
            <td class="text-right" style="font-size: 14px;">$<?= number_format($venta['total'], 2) ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center">
        <?php if (!empty($venta['codigo_externo'])): ?>
            <br>
            <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin: 10px 0;">
                <small>REFERENCIA ESCANEADA:</small><br>
                <strong style="font-size: 14px; font-family: monospace;">
                    <?= htmlspecialchars($venta['codigo_externo']) ?>
                </strong>
            </div>
        <?php endif; ?>
        
        ¡Gracias por su compra!<br>
        <small>Cambios solo con ticket dentro de 24hrs.</small><br>
        <small>Precios con IVA incluido.</small>
        
        <br><br>
        
        <div style="margin-top:5px;">
            <small>Folio del Ticket:</small><br>
            <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= $venta['id'] ?>&code=Code128&dpi=96&thickness=2" 
                 alt="Codigo de Barras" 
                 style="width: 150px; height: 50px;">
            <br>
            <strong style="font-size: 16px; letter-spacing: 2px;">
                <?= str_pad($venta['id'], 6, "0", STR_PAD_LEFT) ?>
            </strong>
        </div>
    </div>
    
    <button class="no-print" onclick="window.close()" 
            style="margin-top:20px; width:100%; padding:15px; background:#ddd; border:none; cursor:pointer;">
        CERRAR TICKET
    </button>

</body>
</html>