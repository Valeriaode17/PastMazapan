<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// === LÓGICA DE FILTROS ===
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'activos'; // Por defecto 'activos'

$condicion = "";
if ($filtro === 'activos') {
    $condicion = " AND i.activo = 1 ";
}
// Si es 'todos', no agregamos condición (muestra activos e inactivos)

// Consultamos items según filtro
$sql = "SELECT i.*, IFNULL(e.cantidad, 0) as stock 
        FROM items i 
        LEFT JOIN existencias e ON i.id = e.id_item 
        WHERE 1=1 $condicion
        ORDER BY i.id DESC";
$result = $conn->query($sql);

// === CÁLCULO DE TOTALES (En memoria) ===
// Guardamos los datos en un array para poder contarlos y luego dibujarlos
$productos = [];
$suma_stock = 0;

while($row = $result->fetch_assoc()) {
    $productos[] = $row;
    $suma_stock += $row['stock'];
}
$total_productos = count($productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Admin</title>
    <link rel="stylesheet" href="style/styles.css">
    <style>
        /* Estilo específico para el LOGO solicitado */
        .logo-admin {
            width: 28mm; /* Entre 24-32mm */
            display: block;
            margin-bottom: 10px;
        }
        
        /* Estilos para la barra de filtros y resumen */
        .resumen-bar {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-primary{
            background:#0275d8;
        }
        .admin-table{
            margin-top:5px;
        }
        .stats { font-weight: bold; color: #333; font-size: 14px; }
        .filter-form { display: flex; gap: 10px; align-items: center; }
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
                <a href="admin.php" class="active">📦 Inventario</a>
                <a href="admin_usuarios.php">👥 Usuarios</a>
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
            

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <h2>Inventario de Productos</h2>
                <div style="display:flex; gap:10px;">
                    <button class="btn btn-success" onclick="document.getElementById('modal').classList.add('active')">+ Nuevo Producto</button>
                </div>
                
            </div>
             
                                <a href="reporte_inventario.php?filtro=<?= $filtro ?>" target="_blank" class="btn btn-primary" >📄 Imprimir Reporte</a>
                                <a href="exportar_inventario.php?filtro=<?= $filtro ?>" class="btn" style="background:#28a745; color:white; text-decoration:none;">📥 CSV Inventario</a>
<form method="GET" class="filter-form">
                    <label><strong>Filtro:</strong></label>
                    <select name="filtro" class="form-control" onchange="this.form.submit()" style="padding:10px; margin-top:10px;">
                        <option value="activos" <?= $filtro=='activos'?'selected':'' ?>>Solo Activos (Venta)</option>
                        <option value="todos" <?= $filtro=='todos'?'selected':'' ?>>Todos (Incluir Inactivos)</option>
                    </select>
                </form>

          

            <table class="admin-table">
                
                <thead>
                    <tr>
                        <th>Img</th>
                        <th>Código</th>
                        <th>Nombre / Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $row): ?>
                    <tr>
                        <td><img src="img_items/<?= $row['imagen'] ?>" width="40" onerror="this.src='https://via.placeholder.com/40'"></td>
                        <td><?= $row['codigo'] ?></td>
                        <td>
                            <strong><?= $row['nombre'] ?></strong><br>
                            <small style="color:#666; font-style: italic;"><?= $row['descripcion'] ?></small>
                        </td>
                        <td>$<?= number_format($row['precio'], 2) ?></td>
                        
                        <td style="<?= $row['stock'] <= 5 ? 'color:red;font-weight:bold;' : '' ?>">
                            <?= $row['stock'] ?>
                        </td>

                        <td>
                            <?php if($row['activo'] == 1): ?>
                                <span style="color:green; font-weight:bold;">Activo</span>
                            <?php else: ?>
                                <span style="color:red; font-weight:bold;">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="backend_borrar_item.php?id=<?= $row['id'] ?>" 
                               onclick="return confirm('¿Eliminar/Desactivar este producto?')"
                               class="btn btn-danger" style="padding: 5px 10px; font-size:12px;">X</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
              <div class="resumen-bar">
                
               

                <div class="stats">
                    Total productos listados: <span style="color:#0275d8;"><?= $total_productos ?></span> <br>
                    Suma de existencias: <span style="color:#2E7D32;"><?= $suma_stock ?></span>
                </div>
            </div>
        </main>
    </div>

    
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nuevo Producto</h3>
                <span class="close-modal" onclick="document.getElementById('modal').classList.remove('active')">&times;</span>
            </div>
            <form action="backend_save_product.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="codigo" class="form-control" placeholder="Código" required style="margin-bottom:10px;">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del Producto" required style="margin-bottom:10px;">
                <textarea name="descripcion" class="form-control" placeholder="Descripción breve..." rows="2" style="margin-bottom:10px; font-family:inherit;"></textarea>
                <label>Imagen del Producto:</label>
                <input type="file" name="imagen" class="form-control" accept="image/*" style="margin-bottom:10px;">
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Precio Venta ($):</label>
                        <input type="number" step="0.5" name="precio" class="form-control" placeholder="0.00" required>
                    </div>
                    <div style="flex:1;">
                        <label>Stock Inicial:</label>
                        <input type="number" name="cantidad" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <br>
                <button type="submit" class="btn btn-success w-100">Guardar Producto</button>
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

</html>