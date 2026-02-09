<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit(); }

$provs = $conn->query("SELECT * FROM proveedores WHERE activo=1");
$items = $conn->query("SELECT * FROM items WHERE activo=1");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Compras</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let compra = [];
        
        function agregar(id, nombre) {
            let costo = prompt("Costo unitario para " + nombre + ":");
            if(!costo) return;
            let cant = prompt("Cantidad a comprar:");
            if(!cant) return;
            
            compra.push({id_item: id, nombre: nombre, costo: parseFloat(costo), cantidad: parseInt(cant)});
            render();
        }

        function render() {
            let html = '', total = 0;
            compra.forEach((p, i) => {
                let sub = p.cantidad * p.costo;
                total += sub;
                html += `<tr><td>${p.cantidad}</td><td>${p.nombre}</td><td>$${p.costo}</td><td>$${sub}</td><td><button class="btn btn-danger" onclick="compra.splice(${i},1);render()">X</button></td></tr>`;
            });
            document.getElementById('lista').innerHTML = html;
            document.getElementById('total').innerText = total.toFixed(2);
        }

        function guardar() {
            let id_prov = document.getElementById('prov').value;
            if(compra.length === 0 || id_prov === "") { alert("Faltan datos"); return; }

            fetch('backend_compra.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id_proveedor: id_prov, items: compra})
            }).then(r=>r.json()).then(d=>{
                if(d.success) { alert("Compra registrada. Inventario actualizado."); location.reload(); }
                else { alert("Error: "+d.msg); }
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
                <a href="admin_usuarios.php">👥 Usuarios</a>
                <a href="admin_proveedores.php">🚚 Proveedores</a>
                <a href="admin_compras.php" class="active">📥 Compras</a>
                <a href="admin_devoluciones.php">↩️ Devoluciones</a>
                <a href="logout.php">Salir</a>
            </div>
        </nav>
        <main class="admin-content">
            <h2>Registrar Compra</h2>
            <div class="form-group">
                <label>Proveedor:</label>
                <select id="prov" class="form-control">
                    <option value="">-- Selecciona --</option>
                    <?php while($p=$provs->fetch_assoc()): ?><option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option><?php endwhile; ?>
                </select>
            </div>
            <div style="display:flex; gap:20px;">
                <div style="flex:1; height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px;">
                    <h4>Productos</h4>
                    <?php while($i=$items->fetch_assoc()): ?>
                        <div style="padding:10px; border-bottom:1px solid #eee; cursor:pointer" onclick="agregar(<?= $i['id'] ?>,'<?= $i['nombre'] ?>')">
                            <b><?= $i['nombre'] ?></b> (<?= $i['codigo'] ?>)
                        </div>
                    <?php endwhile; ?>
                </div>
                <div style="flex:1">
                    <h4>Detalle</h4>
                    <table class="admin-table">
                        <thead><tr><th>Cant</th><th>Prod</th><th>Costo</th><th>Sub</th><th></th></tr></thead>
                        <tbody id="lista"></tbody>
                    </table>
                    <h3>Total: $<span id="total">0.00</span></h3>
                    <button class="btn btn-success w-100" onclick="guardar()">FINALIZAR COMPRA</button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>