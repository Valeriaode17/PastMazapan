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
if (!empty($fecha_ini) && !empty($fecha_fin)) {
    $condicion = " WHERE d.fecha BETWEEN '$fecha_ini 00:00:00' AND '$fecha_fin 23:59:59' ";
    $orden = " ORDER BY d.id ASC ";
} else {
    $orden = " ORDER BY d.id DESC LIMIT 50";
}

$sql = "SELECT d.*, u.nombre as usuario 
        FROM devoluciones d 
        JOIN usuarios u ON d.id_usuario = u.id 
        $condicion $orden";
$devs = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Devoluciones por Rango</title>
    <link rel="stylesheet" href="style/styles.css">
    <style>.filter-box { background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd; display: flex; gap: 10px; align-items: flex-end; }
        .btn-primary{
            background:#0275d8;
        }
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
                <a href="admin_reporte_ventas.php">💰 Historial Ventas</a>
                <a href="admin_historial_compras.php">📋 Historial Compras</a>
                <a href="admin_historial_devoluciones.php"class="active">🔙 Historial Devoluciones</a>

                <a href="logout.php" style="border-top: 1px solid #444; color: #ff8a80; margin-top:20px;">Cerrar Sesión</a>
            </div>
        </nav>

        <main class="admin-content">
            <h2>Devoluciones de venta por rango</h2>

            <form class="filter-box">
                <div><label>Fecha Inicio:</label><br><input type="date" name="f_ini" class="form-control" value="<?= $fecha_ini ?>" required></div>
                <div><label>Fecha Fin:</label><br><input type="date" name="f_fin" class="form-control" value="<?= $fecha_fin ?>" required></div>
                <button type="submit" class="btn btn-success">🔍 Filtrar</button>
                <?php if($fecha_ini): ?><a href="admin_historial_devoluciones.php" class="btn btn-danger">X</a><?php endif; ?>
            </form>

            <div class="no-print">
                <a href="reporte_devoluciones.php?f_ini=<?= $fecha_ini ?>&f_fin=<?= $fecha_fin ?>" target="_blank" class="btn btn-primary">📄 Imprimir Reporte</a>
                <a href="exportar_devoluciones.php" class="btn" style="background:#28a745; color:white; text-decoration:none; padding:10px 18px; border-radius:6px; margin-left:10px;">📊 Exportar CSV</a>
            </div>
            <br>

            <table class="admin-table">
                <thead><tr><th>Folio</th><th>Fecha</th><th>Venta Origen</th><th>Usuario</th></tr></thead>
                <tbody>
                    <?php while($r=$devs->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $r['id'] ?></td>
                        <td><?= $r['fecha'] ?></td>
                        <td>#<?= $r['id_venta'] ?></td>
                        <td><?= $r['usuario'] ?></td>
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