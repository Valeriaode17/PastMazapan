<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role'])) { header("Location: index.php"); exit; }

// Obtener Filtro
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'activos';
$condicion = "";
$texto_filtro = "";

if ($filtro === 'activos') {
    $condicion = " AND i.activo = 1 ";
    $texto_filtro = "Solo Productos Activos";
} else {
    $texto_filtro = "Todos (Activos e Inactivos)";
}

$sql = "SELECT i.*, IFNULL(e.cantidad, 0) as stock 
        FROM items i 
        LEFT JOIN existencias e ON i.id = e.id_item 
        WHERE 1=1 $condicion
        ORDER BY i.nombre ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Inventario</title>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; font-size: 12px; color: #333; }
    
    /* --- NUEVO DISEÑO DE ENCABEZADO --- */
    .header-wrapper {
        position: relative; /* Permite mover el logo libremente */
        min-height: 80px;
        border-bottom: 2px solid #444;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .logo { 
        position: absolute; /* Lo fijamos a la esquina */
        top: 0; 
        left: 0; 
        width: 28mm; /* Tamaño solicitado */
    }

    .header-content {
        text-align: center; /* Texto centrado */
        width: 100%;
        padding-top: 5px;
    }
    /* ---------------------------------- */

    h2 { margin: 5px 0; text-transform: uppercase; letter-spacing: 1px; }
    .info { font-size: 11px; color: #666; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; font-size: 11px; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    
    .badge-filtro {
        background: #eee; padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc;
        font-weight: bold; display: inline-block; margin-top: 5px;
    }

    @media print { .no-print { display: none; } }
    .btn { padding: 8px 15px; cursor: pointer; border: 1px solid #ccc; background: #eee; border-radius: 4px; margin-right: 5px; }
</style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom:15px;">
        <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn" onclick="window.close()">Cerrar Pestaña</button>
    </div>

    <div class="header-wrapper">
        <img src="img_items/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
        
        <div class="header-content">
            <h2>Reporte de Existencias</h2>
            <div class="info">
                Fecha: <?= date("d/m/Y H:i") ?> | Generado por: <?= htmlspecialchars($_SESSION['user_name']) ?>
            </div>
            <div class="badge-filtro">Filtro aplicado: <?= $texto_filtro ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto / Descripción</th>
                <th class="text-right">Precio</th>
                <th class="text-center">Existencia</th>
                <th class="text-right">Valor Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 0;
            $sum_stock = 0;
            $valor_total = 0;

            while($r = $result->fetch_assoc()): 
                $count++;
                $sum_stock += $r['stock'];
                $valor = $r['precio'] * $r['stock'];
                $valor_total += $valor;
            ?>
            <tr>
                <td><?= $r['codigo'] ?></td>
                <td>
                    <strong><?= $r['nombre'] ?></strong><br>
                    <small style="color:#666;"><?= $r['descripcion'] ?></small>
                </td>
                <td class="text-right">$<?= number_format($r['precio'], 2) ?></td>
                <td class="text-center bold" style="<?= $r['stock']<=5 ? 'color:red' : '' ?>">
                    <?= $r['stock'] ?>
                </td>
                <td class="text-right">$<?= number_format($valor, 2) ?></td>
            </tr>
            <?php endwhile; ?>
            
            <tr style="background-color: #e0e0e0; font-weight:bold;">
                <td colspan="3" class="text-right">TOTALES:</td>
                <td class="text-center"><?= $sum_stock ?> unid.</td>
                <td class="text-right">$<?= number_format($valor_total, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <strong>Resumen:</strong><br>
        1. Total de productos listados: <span style="color:#0275d8; font-weight:bold;"><?= $count ?></span><br>
        2. Suma total de existencias: <span style="color:#2E7D32; font-weight:bold;"><?= $sum_stock ?></span>
    </div>

</body>
</html>