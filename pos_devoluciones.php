<?php
session_start();
require 'db.php';
// Seguridad
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo de Devoluciones</title>
    <link rel="stylesheet" href="style/styles.css">
    <style>
        body { background-color: #f4f4f4; margin: 0; font-family: Arial, sans-serif; }
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .search-area { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .search-input { flex: 1; padding: 12px; font-size: 16px; border: 2px solid #ddd; border-radius: 5px; }
        .search-btn { padding: 12px 25px; background: #0275d8; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        
        .dev-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .dev-table th { background: #333; color: white; padding: 12px; text-align: left; }
        .dev-table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .txt-precio { color: #2E7D32; font-weight: bold; }
        .txt-folio { font-size: 1.2rem; font-weight: bold; color: #555; margin-bottom: 10px; }
        .btn-return { background: #d32f2f; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .badge-done { background: #e0e0e0; color: #757575; padding: 8px 15px; border-radius: 4px; font-weight: bold; display: inline-block; }
    </style>
</head>
<body>

<nav style="background: #333; color: white; padding: 0 20px; height: 60px; display: flex; align-items: center; justify-content: space-between;">
    <h3 style="margin:0;">↺ Devoluciones</h3>
    <a href="pos.php" class="btn" style="background:#E67E22; color: white; padding: 8px 15px; text-decoration:none; border-radius:4px; font-weight:bold;">Volver a Caja</a>
</nav>

<div class="container">
    <div class="search-area">
        <input type="number" id="txtFolio" class="search-input" placeholder="Escribe el Folio del Ticket (Ej. 5)" autofocus>
        <button class="search-btn" onclick="buscarTicket()">🔍 Buscar Ticket</button>
    </div>
    <div id="resultados">
        <div style="text-align:center; color:#999; padding: 40px;">Ingresa el número de folio para ver los productos.</div>
    </div>
</div>

<script>
    document.getElementById('txtFolio').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') buscarTicket();
    });

    function buscarTicket() {
        let folio = document.getElementById('txtFolio').value;
        if(!folio) return alert("Por favor escribe un número de folio.");
        let contenedor = document.getElementById('resultados');
        contenedor.innerHTML = '<div style="text-align:center; padding:20px;">Buscando información...</div>';

        fetch('backend_buscar_ticket.php?folio=' + folio)
        .then(r => r.json())
        .then(data => {
            if(data.length === 0) {
                contenedor.innerHTML = '<div style="text-align:center; color:red; padding:20px; font-weight:bold;">❌ Ticket no encontrado o vacío.</div>';
                return;
            }
            let html = `<div class="txt-folio">Ticket #${folio}</div>
                <table class="dev-table">
                    <thead><tr>
                        <th style="width:10%;">Cant. Vendida</th>
                        <th style="width:50%;">Producto</th>
                        <th style="width:20%;">Precio Unit.</th>
                        <th style="width:20%; text-align:center;">Acción</th>
                    </tr></thead><tbody>`;
            
            data.forEach(item => {
                let accionHtml = '';
                if (item.devuelto == 1) {
                    accionHtml = '<span class="badge-done">🚫 YA DEVUELTO</span>';
                } else {
                    // Pasamos la cantidad máxima vendida
                    accionHtml = `<button class="btn-return" onclick="devolver(${folio}, ${item.id_item}, ${item.cantidad}, '${item.nombre}')">↺ DEVOLVER</button>`;
                }
                html += `<tr>
                        <td style="text-align:center; font-weight:bold;">${item.cantidad}</td>
                        <td>${item.nombre}<br><small style="color:#888;">Cód: ${item.id_item}</small></td>
                        <td class="txt-precio">$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                        <td style="text-align:center;">${accionHtml}</td>
                    </tr>`;
            });
            html += `</tbody></table>`;
            contenedor.innerHTML = html;
        });
    }

    function devolver(folio, idItem, cantMax, nombre) {
        // 1. PEDIR CANTIDAD
        let cantStr = prompt(`Vas a devolver: ${nombre}.\nCantidad vendida: ${cantMax}.\n\n¿Cuántos items devuelve el cliente?`, cantMax);
        if (cantStr === null) return; // Cancelado
        
        let cant = parseInt(cantStr);
        if (isNaN(cant) || cant <= 0) { alert("Cantidad inválida"); return; }
        if (cant > cantMax) { alert("No puedes devolver más de lo que se vendió."); return; }

        // 2. PEDIR MOTIVO
        let motivo = prompt("Motivo de la devolución:", "Defecto / Cambio");
        if (motivo === null) motivo = "Sin especificar";

        fetch('backend_devolucion.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            // Enviamos cantidad elegida y motivo
            body: JSON.stringify({ id_venta: folio, id_item: idItem, cantidad: cant, motivo: motivo })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                alert("✅ Devolución exitosa. Stock actualizado.");
                buscarTicket();
            } else {
                alert("❌ Error: " + res.msg);
            }
        });
    }
</script>
</body>
</html>