<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit(); }

$result = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Admin</title>
    <link rel="stylesheet" href="style/styles.css">
    <style>

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .alert-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
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
                <a href="admin.php">📦 Inventario</a>
                <a href="admin_usuarios.php" class="active">👥 Usuarios</a>
                <a href="admin_proveedores.php">🚚 Proveedores</a>
                
                <a href="admin_compras.php" style="color:#A5D6A7;">📥 + Registrar Compra</a>
                <a href="admin_devoluciones.php" style="color:#FFCC80;">↩️ + Nueva Devolución</a>
   
                <div style="padding:10px 20px; color:#aaa; font-size:0.8rem; margin-top:10px;">REPORTES</div>
                <a href="admin_reporte_ventas.php">💰 Historial Ventas</a>
                <a href="admin_historial_compras.php">📋 Historial Compras</a>
                <a href="admin_historial_devoluciones.php">🔙 Historial Devoluciones</a>

                <a href="logout.php" style="border-top: 1px solid #444; color: #ff8a80; margin-top:20px;">Cerrar Sesión</a>
            </div>
        </nav>

        <main class="admin-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2>Gestión de Usuarios</h2>
                <button class="btn btn-success" onclick="document.getElementById('modal-usuario').classList.add('active')">+ Nuevo Usuario</button>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nombre'] ?></td>
                        <td><?= $row['correo'] ?></td>
                        <td>
                            <?php if($row['rol'] == 'admin'): ?>
                                <span class="badge" style="background:#673AB7; color:white; padding:3px 8px; border-radius:4px;">ADMIN</span>
                            <?php else: ?>
                                <span class="badge" style="background:#009688; color:white; padding:3px 8px; border-radius:4px;">CAJERO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="backend_borrar_usuario.php?id=<?= $row['id'] ?>" 
                               onclick="return confirm('¿Seguro que deseas eliminar al usuario <?= $row['nombre'] ?>?')"
                               class="btn btn-danger" style="padding:5px 10px; font-size:12px;">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
    
    <div id="modal-usuario" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Nuevo Usuario</h3><span class="close-modal" onclick="document.getElementById('modal-usuario').classList.remove('active')">&times;</span></div>
            <form action="backend_save_user.php" method="POST">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre" required style="margin-bottom:10px;">
                <input type="email" name="correo" class="form-control" placeholder="Correo" required style="margin-bottom:10px;">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required style="margin-bottom:10px;">
                <select name="rol" class="form-control" style="margin-bottom:10px;">
                    <option value="usuario">Cajero</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn btn-success w-100">Guardar</button>
            </form>
        </div>
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