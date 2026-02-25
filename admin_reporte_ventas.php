<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role'])) { header("Location: index.php"); exit; }

// === LÓGICA DE FILTROS ===
$fecha_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : '';
$fecha_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : '';

$condicion = "";
if (!empty($fecha_ini) && !empty($fecha_fin)) {
    // Filtro por rango
    $condicion = " WHERE v.fecha BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ";
} else {
    // Por defecto: Últimas 50
    $condicion = " ORDER BY v.id DESC LIMIT 50";
}

// Si hay filtro, ordenamos por fecha ascendente, si no, descendente
$orden = (!empty($fecha_ini)) ? " ORDER BY v.id ASC " : "";

$sql = "SELECT v.*, u.nombre as cajero 
        FROM ventas v 
        JOIN usuarios u ON v.id_usuario = u.id 
        $condicion $orden";
$ventas = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Ventas</title>
<link rel="stylesheet" href="style/styles.css">
<style>
    .filter-box { background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd; display: flex; gap: 10px; align-items: flex-end; }
    .btn-imprimir { background: #0275d8; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; }
</style>
</head>
<body>
<div class="admin-layout">
      <div class="menu-overlay" onclick="toggleMenu()"></div>
    <button class="hamburger-btn" onclick="toggleMenu()">&#9776;</button>
    

 <nav class="sidebar">
            <div style="padding: 20px;">
                <h3>Panel Admin</h3>
                <small>Hola, <?= htmlspecialchars($_SESSION['user_name']); ?></small>
            </div>
            <div class="menu">
                <a href="admin.php" >📦 Inventario</a>
                <a href="admin_usuarios.php">👥 Usuarios</a>
                <a href="admin_proveedores.php">🚚 Proveedores</a>
                
                <a href="admin_compras.php" style="color:#A5D6A7;">📥 + Registrar Compra</a>
                <a href="admin_devoluciones.php" style="color:#FFCC80;">↩️ + Nueva Devolución</a>
                
                <div style="padding:10px 20px; color:#aaa; font-size:0.8rem; margin-top:10px;">REPORTES</div>
                <a href="admin_reporte_ventas.php" class="active">💰 Historial Ventas</a>
                <a href="admin_historial_compras.php">📋 Historial Compras</a>
                <a href="admin_historial_devoluciones.php">🔙 Historial Devoluciones</a>

                <a href="logout.php" style="border-top: 1px solid #444; color: #ff8a80; margin-top:20px;">Cerrar Sesión</a>
            </div>
        </nav>

    <main class="admin-content">
        <h2>Ventas por Rango</h2>
        
        <form class="filter-box">
            <div>
                <label>Fecha Inicio:</label><br>
                <input type="date" name="f_ini" class="form-control" value="<?= $fecha_ini ?>" required>
            </div>
            <div>
                <label>Fecha Fin:</label><br>
                <input type="date" name="f_fin" class="form-control" value="<?= $fecha_fin ?>" required>
            </div>
            <button type="submit" class="btn btn-success">🔍 Filtrar</button>    
    

            <?php if($fecha_ini): ?>
                <a href="admin_reporte_ventas.php" class="btn btn-danger">X Limpiar</a>
              

            <?php endif; ?>
        </form>

        <div class="no-print">
            <a href="reporte_ventas.php?f_ini=<?= $fecha_ini ?>&f_fin=<?= $fecha_fin ?>" target="_blank" class="btn-imprimir">📄 Imprimir Reporte</a>
            <a href="exportar_ventas.php" class="btn" style="background:#28a745; color:white; text-decoration:none; padding:10px 18px; border-radius:6px; margin-left:10px;">📊 Exportar CSV</a>
        </div>
        <br>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Cajero</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $ventas->fetch_assoc()): ?>
                <tr>
                    <td>#<?= str_pad($r['id'], 6, "0", STR_PAD_LEFT) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($r['fecha'])) ?></td>
                    <td><?= $r['cajero'] ?></td>
                    <td>$<?= number_format($r['total'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>
 <script>
    function toggleMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.menu-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
</script>
</body>
</html>