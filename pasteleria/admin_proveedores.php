<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit(); }

// Guardar Proveedor (Lógica simple en el mismo archivo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nombre']; $cor = $_POST['correo']; $tel = $_POST['telefono'];
    $conn->query("INSERT INTO proveedores (nombre, correo, telefono) VALUES ('$nom', '$cor', '$tel')");
    header("Location: admin_proveedores.php"); exit;
}

$provs = $conn->query("SELECT * FROM proveedores WHERE activo = 1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Proveedores</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="sidebar">
            <div style="padding:20px;"><h3>Panel Admin</h3></div>
            <div class="menu">
                <a href="admin.php">📦 Inventario</a>
                <a href="admin_usuarios.php">👥 Usuarios</a>
                <a href="admin_proveedores.php" class="active">🚚 Proveedores</a>
                <a href="admin_compras.php">📥 Compras</a>
                <a href="admin_devoluciones.php">↩️ Devoluciones</a>
                <a href="logout.php">Salir</a>
            </div>
        </nav>
        <main class="admin-content">
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <h2>Proveedores</h2>
                <button class="btn btn-primary" onclick="document.getElementById('modal').style.display='flex'">+ Nuevo</button>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th></tr></thead>
                <tbody>
                    <?php while($r=$provs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= $r['nombre'] ?></td>
                        <td><?= $r['correo'] ?></td>
                        <td><?= $r['telefono'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <h3>Nuevo Proveedor</h3>
            <form method="POST">
                <input type="text" name="nombre" class="form-control" placeholder="Empresa/Nombre" required>
                <input type="email" name="correo" class="form-control" placeholder="Correo">
                <input type="text" name="telefono" class="form-control" placeholder="Teléfono">
                <div class="text-right">
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('modal').style.display='none'">C</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>