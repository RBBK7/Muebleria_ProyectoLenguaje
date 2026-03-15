<?php
session_start();
require_once 'Conexion/conexion.php';
include __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->getConnection();

// Obtener estadísticas
$stats = [];

// Total productos
$query = "SELECT COUNT(*) as total FROM MUEBLERIA.PRODUCTO";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['productos'] = $row['TOTAL'];

// Total clientes
$query = "SELECT COUNT(*) as total FROM MUEBLERIA.CLIENTE";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['clientes'] = $row['TOTAL'];

// Total pedidos
$query = "SELECT COUNT(*) as total FROM MUEBLERIA.PEDIDO";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['pedidos'] = $row['TOTAL'];

// Pedidos recientes
$query = "SELECT p.ID_PEDIDO, p.FECHA, p.TOTAL, p.ESTADO, 
                 c.NOMBRE as CLIENTE, u.NOMBRE as USUARIO
          FROM MUEBLERIA.PEDIDO p
          JOIN MUEBLERIA.CLIENTE c ON p.ID_CLIENTE = c.ID_CLIENTE
          JOIN MUEBLERIA.USUARIO u ON p.ID_USUARIO = u.ID_USUARIO
          ORDER BY p.FECHA DESC
          FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
$pedidos_recientes = [];
while ($row = oci_fetch_assoc($stmt)) {
    $pedidos_recientes[] = $row;
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="display-4">Bienvenido al Sistema de Mueblería</h1>
        <p class="lead">Gestione sus productos, clientes, pedidos y más.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <i class="fas fa-couch"></i>
            <div class="stats-number"><?php echo $stats['productos']; ?></div>
            <div class="stats-label">Productos</div>
            <a href="modules/productos/productos.php" class="btn btn-light mt-3">Ver Productos</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <i class="fas fa-users"></i>
            <div class="stats-number"><?php echo $stats['clientes']; ?></div>
            <div class="stats-label">Clientes</div>
            <a href="modules/clientes/clientes.php" class="btn btn-light mt-3">Ver Clientes</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <i class="fas fa-shopping-cart"></i>
            <div class="stats-number"><?php echo $stats['pedidos']; ?></div>
            <div class="stats-label">Pedidos</div>
            <a href="modules/pedidos/pedidos.php" class="btn btn-light mt-3">Ver Pedidos</a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <i class="fas fa-boxes"></i>
            <div class="stats-number"><?php echo $stats['productos']; ?></div>
            <div class="stats-label">Inventario</div>
            <a href="modules/inventario/inventario.php" class="btn btn-light mt-3">Ver Inventario</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock"></i> Pedidos Recientes
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['ID_PEDIDO']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['FECHA'])); ?></td>
                            <td><?php echo $pedido['CLIENTE']; ?></td>
                            <td>₡<?php echo number_format($pedido['TOTAL'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                if ($pedido['ESTADO'] == 'ENTREGADO') $badge_class = 'badge-success';
                                elseif ($pedido['ESTADO'] == 'PENDIENTE') $badge_class = 'badge-warning';
                                elseif ($pedido['ESTADO'] == 'CANCELADO') $badge_class = 'badge-danger';
                                else $badge_class = 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $pedido['ESTADO']; ?>
                                </span>
                            </td>
                            <td><?php echo $pedido['USUARIO']; ?></td>
                            <td>
                                <a href="modules/pedidos/detalle.php?id=<?php echo $pedido['ID_PEDIDO']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Acciones Rápidas
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="modules/productos/nuevo.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                    <a href="modules/clientes/nuevo.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Nuevo Cliente
                    </a>
                    <a href="modules/pedidos/nuevo.php" class="btn btn-success">
                        <i class="fas fa-cart-plus"></i> Nuevo Pedido
                    </a>
                    <a href="modules/proveedores/nuevo.php" class="btn btn-success">
                        <i class="fas fa-truck"></i> Nuevo Proveedor
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Información
            </div>
            <div class="card-body">
                <p><i class="fas fa-calendar"></i> Fecha: <?php echo date('d/m/Y'); ?></p>
                <p><i class="fas fa-clock"></i> Hora: <?php echo date('h:i A'); ?></p>
                <p><i class="fas fa-user"></i> Usuario: Administrador</p>
            </div>
        </div>
    </div>
</div>

<?php
oci_free_statement($stmt);
$db->close();
include 'includes/footer.php';
?>