<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT i.id, i.codigo, i.nombre, i.descripcion, i.precio, i.imagen, IFNULL(e.cantidad, 0) as cantidad 
        FROM items i 
        LEFT JOIN existencias e ON i.id = e.id_item 
        WHERE i.activo = 1";
$result = $conn->query($sql);

$productos_db = [];
while($row = $result->fetch_assoc()) {
    $productos_db[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Punto de Venta</title>
    <link rel="stylesheet" href="style/styles.css?v=<?= time() ?>"> 

    <style>
        body { overflow: hidden; margin: 0; padding: 0; }
        
        /* Ajuste para que el nav sea relativo y permita posicionar el menú móvil */
        .pos-navbar {
            background: var(--primary); color: white; padding: 0 20px; 
            display: flex; justify-content: space-between; align-items: center; height: 60px;
            position: relative; /* Necesario para el menú desplegable */
        }

        .pos-layout { display: flex; flex-direction: row; flex-wrap: nowrap; height: calc(100vh - 60px); width: 100%; }
        .pos-products { flex-grow: 1; order: 1; padding: 10px; overflow-y: auto; background: #f4f4f4; }
        .pos-ticket { width: 380px; min-width: 380px; order: 2; background: white; border-left: 2px solid #ccc; display: flex; flex-direction: column; z-index: 10; }
        .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
        
        .product-card { background: white; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; padding-bottom: 8px; overflow: hidden; transition: transform 0.1s; display: flex; flex-direction: column; justify-content: space-between; height: 250px; }
        
        .product-card:active { transform: scale(0.98); }
        .product-card img { width: 100%; height: 90px; object-fit: contain; border-bottom: 1px solid #eee; margin-bottom: 5px; }
        
        .product-info { padding: 0 5px; flex-grow: 1; display: flex; flex-direction: column; }
        .product-card h4 { font-size: 13px; margin: 0; color: #333; line-height: 1.2; font-weight: bold; }
        .product-desc { font-size: 11px; color: #777; margin: 4px 0; font-style: italic; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .price-tag { color: #E67E22; font-weight: bold; font-size: 1.1em; margin-top: auto; }
        
        .ticket-header { padding: 15px; background: #333; color: white; text-align: center; }
        .ticket-body { flex: 1; overflow-y: auto; padding: 10px; }
        .ticket-footer { padding: 15px; background: #eee; border-top: 1px solid #ccc; }
        .cart-item { display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .cart-img { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; margin-right: 10px; }

        .external-code-box { margin-bottom: 10px; padding: 5px; background: #fff; border: 1px solid #ccc; }
        .external-code-box label { display: block; font-size: 10px; font-weight: bold; color: #555; }
        .external-code-box input { width: 100%; padding: 5px; font-weight: bold; text-align: center; border: 1px solid #ddd; }
    </style>
</head>
<body>

<nav class="pos-navbar">
    <div style="font-size: 1.2rem; font-weight: bold;">
        <h3 style="margin:0">Caja</h3>
    </div>

    <button class="pos-menu-btn" onclick="toggleNav()">&#9776;</button>

    <div class="pos-nav-links" id="posNav">
        <a href="pos_devoluciones.php" class="btn" style="background: #E67E22; color: white; margin-right: 10px; text-decoration: none;">↺ Devoluciones</a>
        <button onclick="verUltimasVentas()" class="btn btn-secondary">📜 Últimas Ventas</button>
        <a href="logout.php" class="btn btn-danger" style="margin-left: 10px;">Salir</a>
    </div>
</nav>

<div class="pos-layout">
    <div class="pos-products">
        <div class="pos-grid">
        <?php foreach($productos_db as $p): ?>
            <div class="product-card" onclick="agregar('<?= $p['codigo'] ?>')"> 
                <img src="img_items/<?= $p['imagen'] ?: 'default.png' ?>" alt="Foto" onerror="this.src='https://via.placeholder.com/150'">
                
                <div class="product-info">
                    <h4><?= $p['nombre'] ?></h4>
                    <div class="product-desc"><?= $p['descripcion'] ?></div>
                    <div style="font-size:10px; color:#aaa; margin-top:2px;"><?= $p['codigo'] ?></div> 
                </div>

                <div class="price-tag">$<?= number_format($p['precio'], 2) ?></div>
                
                <?php if($p['cantidad'] > 0): ?>
                    <small style="color:green; font-size:11px;">Stock: <?= $p['cantidad'] ?></small>
                <?php else: ?>
                    <small style="color:red; font-weight:bold; font-size:11px;">AGOTADO</small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <div class="pos-ticket">
        <div class="ticket-header">
            <h3 style="margin:0">Venta en Curso</h3>
        </div>
        <div id="cart-list" class="ticket-body">
            <div style="text-align:center; color:#999; margin-top:50px;">
                <p>Carrito vacío</p>
                <small>Selecciona un producto de la lista</small>
            </div>
        </div>
        <div class="ticket-footer">
            
            <div class="external-code-box">
                <label>CÓDIGO REFERENCIA (ESCÁNER)</label>
                <input type="text" id="codigoExternoInput" placeholder="Escanea aquí..." autocomplete="off">
            </div>

            <div style="display:flex; justify-content:space-between; font-size:1.4rem; font-weight:bold; margin-bottom:10px;">
                <span>Total:</span>
                <span id="total-lbl">$0.00</span>
            </div>
            <button id="btn-cobrar-f10" class="btn btn-success w-100" style="height:50px; font-size:1.2rem;" onclick="cobrar()">COBRAR (F10)</button>
        </div>
    </div>
</div>

<div id="modalVentas" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; width:450px; border-radius:8px; overflow:hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <div style="background:#333; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">Últimas 10 Ventas</h3>
            <button onclick="document.getElementById('modalVentas').style.display='none'" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <ul id="lista-ventas" style="list-style:none; padding:0; margin:0; max-height:400px; overflow-y:auto;"></ul>
        <div style="padding:10px; text-align:right; background:#eee;">
            <button class="btn btn-danger" onclick="document.getElementById('modalVentas').style.display='none'">Cerrar</button>
        </div>
    </div>
</div>

<script>
const productosDB = <?php echo json_encode($productos_db); ?>;
let carrito = [];
let procesandoCobro = false;

// === NUEVO: FUNCIÓN PARA EL MENÚ MÓVIL ===
function toggleNav() {
    document.getElementById('posNav').classList.toggle('active');
}

let barcodeBuffer = "";
let barcodeTimer = null;
let ultimoCodigoProcesado = "";
let tiempoUltimoProceso = 0;

document.addEventListener("keydown", function(e) {

    if (e.target.id === 'codigoExternoInput') {
        return; 
    }
    
    if (e.key === "F10") {
        e.preventDefault();
        cobrar();
        return;
    }
    
    if (e.key === "Enter") {
        if (barcodeBuffer.length > 0) {
            e.preventDefault();
            procesarLectura(barcodeBuffer);
            barcodeBuffer = ""; // Limpiar
        }
        return;
    }

    if (e.key.length === 1) {
        if (!barcodeTimer) {
            barcodeBuffer = "";
        }
        clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(() => {
            barcodeBuffer = ""; 
        }, 200);

        barcodeBuffer += e.key;
    }
});

function procesarLectura(codigo) {
    codigo = codigo.trim();
    if (!codigo) return;
    
    let ahora = new Date().getTime();
    if (codigo === ultimoCodigoProcesado && (ahora - tiempoUltimoProceso) < 1000) {
        console.log("Lectura duplicada ignorada: " + codigo);
        return;
    }

    ultimoCodigoProcesado = codigo;
    tiempoUltimoProceso = ahora;

    agregar(codigo);
}

document.getElementById('codigoExternoInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); }
});

function agregar(codigo) {
    let producto = productosDB.find(p => p.codigo == codigo);
    if (!producto) { 
        console.warn("Producto no encontrado: " + codigo);
        return; 
    }
    
    let itemEnCarrito = carrito.find(i => i.id === producto.id);
    let stock = parseInt(producto.cantidad);

    if (itemEnCarrito) {
        if (itemEnCarrito.cantidad + 1 > stock) { 
            alert("Stock insuficiente"); 
            return; 
        }
        itemEnCarrito.cantidad++;
    } else {
        if (stock < 1) { 
            alert("Producto Agotado"); 
            return; 
        }
        carrito.push({
            id: producto.id, 
            nombre: producto.nombre, 
            codigo: producto.codigo,
            precio: parseFloat(producto.precio), 
            cantidad: 1, 
            imagen: producto.imagen || 'default.png'
        });
    }
    render();
}

function render() {
    let html = '';
    let total = 0;
    
    if(carrito.length === 0) {
        document.getElementById('cart-list').innerHTML = '<div style="text-align:center; color:#999; margin-top:50px;"><p>Carrito vacío</p></div>';
        document.getElementById('total-lbl').innerText = '$0.00';
        return;
    }

    carrito.forEach((p, index) => {
        let sub = p.cantidad * p.precio;
        total += sub;
        html += `
        <div class="cart-item">
            <img src="img_items/${p.imagen}" class="cart-img" onerror="this.src='https://via.placeholder.com/50'">
            <div style="flex:1;">
                <div style="font-weight:bold; font-size:0.9rem;">${p.nombre}</div>
                <div style="font-size:0.75rem; color:#666;">Código: ${p.codigo}</div>
                <div style="font-size:0.85rem;">$${p.precio} x ${p.cantidad} = <b>$${sub.toFixed(2)}</b></div>
            </div>
            <button class="btn btn-danger" onclick="carrito.splice(${index},1); render()" style="padding:2px 8px;">X</button>
        </div>`;
    });

    document.getElementById('cart-list').innerHTML = html;
    document.getElementById('total-lbl').innerText = '$' + total.toFixed(2);
}

function cobrar() {
    if (procesandoCobro) return;
    if(carrito.length === 0) return;
    
    let totalTxt = document.getElementById('total-lbl').innerText;
    if(!confirm("¿Cobrar venta por " + totalTxt + "?")) return;

    procesandoCobro = true;
    let btn = document.getElementById('btn-cobrar-f10');
    btn.innerText = "Procesando...";
    btn.disabled = true;

    let codigoExtra = document.getElementById('codigoExternoInput').value.trim();

    fetch('backend_process_sale.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ items: carrito, codigo_externo: codigoExtra })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            carrito = [];
            document.getElementById('codigoExternoInput').value = "";
            render();
            window.open('ticket.php?folio=' + data.folio, '_blank');
            location.reload(); 
        } else {
            alert("Error: " + data.message);
            procesandoCobro = false;
            btn.innerText = "COBRAR (F10)";
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert("Error de conexión");
        procesandoCobro = false;
        btn.innerText = "COBRAR (F10)";
        btn.disabled = false;
    });
}

function verUltimasVentas() {
    let modal = document.getElementById('modalVentas');
    let lista = document.getElementById('lista-ventas');
    modal.style.display = 'flex';
    lista.innerHTML = '<li style="padding:20px; text-align:center;">Cargando...</li>';

    fetch('backend_ultimas_ventas.php?nocache=' + new Date().getTime())
    .then(r => r.json())
    .then(json => {
        if (json.success === true && json.data && json.data.length > 0) {
            let html = '';
            json.data.forEach(v => {
                let cajero = v.cajero || 'Sistema';
                html += `
                <li style="border-bottom:1px solid #eee; padding:12px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:bold; color:#333;">Folio #${v.id}</div>
                        <div style="font-size:12px; color:#666;">${v.fecha} - ${cajero}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:1.1em; color:green; font-weight:bold;">$${parseFloat(v.total).toFixed(2)}</div>
                        <a href="ticket.php?folio=${v.id}&tipo=COPIA" target="_blank" 
                           style="display:inline-block; margin-top:4px; font-size:11px; text-decoration:none; background:#007bff; color:white; padding:3px 8px; border-radius:3px;">Ver Ticket</a>
                    </div>
                </li>`;
            });
            lista.innerHTML = html;
        } else {
            lista.innerHTML = '<li style="padding:20px; text-align:center; color:#666;">No hay ventas recientes.</li>';
        }
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>