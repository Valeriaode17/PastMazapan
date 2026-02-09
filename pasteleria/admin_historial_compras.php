<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_role'])) { header("Location: index.php"); exit; }

$sql = "SELECT c.*, p.nombre as proveedor 
        FROM compras c 
        JOIN proveedores p ON c.id_proveedor = p.id 
        ORDER BY c.id DESC";
$compras = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Historial Compras</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        #modalDetalle { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .detalle-content { background:white; padding:20px; width:500px; border-radius:8px; }
    </style>
    <script>
        function verDetalle(id) {
            fetch(`backend_ver_detalles.php?tipo=compra&id=${id}`)
            .then(r => r.json())
            .then(data => {
                let html = '<table class="admin-table"><thead><tr><th>Cant</th><th>Prod</th><th>Costo</th></tr></thead><tbody>';
                data.forEach(d => {
                    html += `<tr><td>${d.cantidad}</td><td>${d.nombre}</td><td>$${d.precio_unitario}</td></tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('cuerpo-detalle').innerHTML = html;
                document.getElementById('modalDetalle').style.display = 'flex';
            });
        }
    </script>
</head>
<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div style="padding:20px;"><h3>Panel Admin</h3></div>
            <div class="menu">
                <a href="admin.php">📦 Inventario</a>
                <a href="admin_reporte_ventas.php">💰 Historial Ventas</a>
                <a href="admin_historial_compras.php" class="active">🚚 Historial Compras</a>
                <a href="admin_compras.php" style="font-size:0.9em; padding-left:30px;">+ Nueva Compra</a>
                <a href="admin_historial_devoluciones.php">↩️ Historial Devoluciones</a>
                <a href="logout.php">Salir</a>
            </div>
        </nav>
        <main class="admin-content">
            <h2>Historial de Compras a Proveedores</h2>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Fecha</th><th>Proveedor</th><th>Total</th><th>Detalles</th></tr></thead>
                <tbody>
                    <?php while($r=$compras->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= $r['fecha'] ?></td>
                        <td><?= $r['proveedor'] ?></td>
                        <td>$<?= number_format($r['total'], 2) ?></td>
                        <td><button class="btn btn-primary" onclick="verDetalle(<?= $r['id'] ?>)">Ver Items</button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="modalDetalle">
        <div class="detalle-content">
            <div style="display:flex; justify-content:space-between;">
                <h3>Mercancía Recibida</h3>
                <button class="btn btn-danger" onclick="document.getElementById('modalDetalle').style.display='none'">X</button>
            </div>
            <div id="cuerpo-detalle"></div>
        </div>
    </div>
</body>
</html>