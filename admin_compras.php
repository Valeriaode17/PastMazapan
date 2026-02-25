<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // ...lo mandamos a la pantalla de login
    header("Location: index.php?error=Acceso denegado");
    exit(); // Detiene la ejecución del resto de la página
}
require 'db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit(); }

$provs = $conn->query("SELECT * FROM proveedores WHERE activo=1");
$items = $conn->query("SELECT * FROM items WHERE activo=1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Registrar Compra (Reabastecer)</title>
    <link rel="stylesheet" href="style/styles.css">
    <script>

        let compra = [];

        function agregar(id, nombre) {
            
            let costo = 300; 

            let cant = prompt("Cantidad a comprar de " + nombre + " ($" + costo + "):");
            
            if(!cant || cant <= 0) return;
            
            compra.push({
                id_item: id, 
                nombre: nombre, 
                costo: parseFloat(costo), 
                cantidad: parseInt(cant)
            });
            
            render();
        }

        function render() {
            let html = '';
            let total = 0;
            compra.forEach((c, i) => {
                let sub = c.costo * c.cantidad;
                total += sub;
                html += `<tr>
                    <td>${c.cantidad}</td>
                    <td>${c.nombre}</td>
                    <td>$${c.costo.toFixed(2)}</td>
                    <td>$${sub.toFixed(2)}</td>
                    <td><button class='btn btn-danger' onclick='eliminar(${i})'>X</button></td>
                </tr>`;
            });
            document.getElementById('lista').innerHTML = html;
            document.getElementById('total').innerText = total.toFixed(2);
        }

        function eliminar(i) {
            compra.splice(i, 1);
            render();
        }

        function guardar() {
            if(compra.length === 0) { alert("Lista vacía"); return; }
            let prov = document.getElementById('prov').value;
            if(!prov) { alert("Selecciona un proveedor"); return; }

            if(!confirm("¿Confirmar compra y aumentar stock?")) return;

            fetch('backend_compra.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_proveedor: prov, items: compra })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("¡Stock actualizado correctamente!");
                    window.location.href = 'admin.php'; 
                } else {
                    alert("Error: " + data.msg);
                }
            });
        }
    </script>
</head>
<body>
    <div class="admin-layout">
          <div class="menu-overlay" onclick="toggleMenu()"></div>
    <button class="hamburger-btn" onclick="toggleMenu()">&#9776;</button>
       <nav class="sidebar">
    <div style="padding: 20px;">
        <h3>Panel Admin</h3>
        <small>Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></small>
    </div>
    <div class="menu">
        <a href="admin.php" >📦 Inventario</a>
        <a href="admin_usuarios.php">👥 Usuarios</a>
        <a href="admin_proveedores.php">🚚 Proveedores</a>
        
        <a href="admin_compras.php" style="color:#A5D6A7;" class="active">📥 + Registrar Compra</a>
        <a href="admin_devoluciones.php" style="color:#FFCC80;">↩️ + Nueva Devolución</a>

        <div style="padding:10px 20px; color:#aaa; font-size:0.8rem; margin-top:10px;">REPORTES</div>
        <a href="admin_reporte_ventas.php">💰 Historial Ventas</a>
        <a href="admin_historial_compras.php">📋 Historial Compras</a>
        <a href="admin_historial_devoluciones.php">🔙 Historial Devoluciones</a>

        <a href="logout.php" style="border-top: 1px solid #444; color: #ff8a80; margin-top:20px;">Cerrar Sesión</a>
    </div>
</nav>

        <main class="admin-content">
            <h2>Reabastecer Inventario</h2>
            <div class="form-group">
                <label>Proveedor:</label>
                <select id="prov" class="form-control">
                    <option value="">-- Selecciona --</option>
                    <?php while($p=$provs->fetch_assoc()): ?><option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option><?php endwhile; ?>
                </select>
            </div>
            <div style="display:flex; gap:20px; margin-top:15px;">
                <div style="flex:1; height:500px; overflow-y:auto; border:1px solid #ccc; padding:20px; background:white; border-radius:5px;">
                    <h4>Selecciona Productos</h4>
                    <?php while($i=$items->fetch_assoc()): ?>
                        <div style="padding:10px; border-bottom:1px solid #eee; cursor:pointer; display:flex; justify-content:space-between; align-items:center;" 
                             onclick="agregar(<?= $i['id'] ?>,'<?= $i['nombre'] ?>')">
                            <div>
                                <b><?= $i['nombre'] ?></b><br>
                                <small style="color:#666;"><?= $i['codigo'] ?></small>
                            </div>
                            <span style="background:#eee; padding:2px 8px; border-radius:10px; font-size:12px;">+</span>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="flex:1; background:white; padding:5px; border-radius:5px; border:1px solid #ddd;">
                    <h4>Detalle de Compra</h4>
                    <table class="admin-table">
                        <thead><tr><th>Cant</th><th>Prod</th><th>Costo</th><th>Sub</th><th></th></tr></thead>
                        <tbody id="lista"></tbody>
                    </table>
                    <div style="margin-top:20px; text-align:right;">
                         <h3>Total Compra: $<span id="total">0.00</span></h3>
                    </div>
                    <button class="btn btn-success w-100" style="margin-top:10px;" onclick="guardar()">✅ FINALIZAR Y SURTIR</button>
                </div>
            </div>
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