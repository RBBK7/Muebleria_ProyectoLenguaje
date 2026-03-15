<?php
session_start();
require_once __DIR__ . '/../../Conexion/conexion.php';
include __DIR__ . '/../../includes/header.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /Muebleria_Proyecto/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? 0;

// Obtener datos del cliente
$query = "SELECT * FROM MUEBLERIA.CLIENTE WHERE ID_CLIENTE = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id);
oci_execute($stmt);
$cliente = oci_fetch_assoc($stmt);

if (!$cliente) {
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no encontrado',
            text: 'El cliente que intenta editar no existe',
            confirmButtonColor: '#2c3e50'
        }).then((result) => {
            window.location.href = 'clientes.php';
        });
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $id = $_POST['id'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($telefono)) $errores[] = "El teléfono es requerido";
    if (empty($correo)) $errores[] = "El correo es requerido";
    if (empty($direccion)) $errores[] = "La dirección es requerida";
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (empty($errores)) {
        // Actualizar cliente
        $query = "UPDATE MUEBLERIA.CLIENTE 
                  SET NOMBRE = :nombre, TELEFONO = :telefono, 
                      CORREO = :correo, DIRECCION = :direccion
                  WHERE ID_CLIENTE = :id";
        
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':id', $id);
        oci_bind_by_name($stmt, ':nombre', $nombre);
        oci_bind_by_name($stmt, ':telefono', $telefono);
        oci_bind_by_name($stmt, ':correo', $correo);
        oci_bind_by_name($stmt, ':direccion', $direccion);
        
        if (oci_execute($stmt)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Cliente actualizado!',
                    text: 'El cliente \"$nombre\" ha sido actualizado exitosamente',
                    confirmButtonColor: '#2c3e50'
                }).then((result) => {
                    window.location.href = 'clientes.php';
                });
            </script>";
        } else {
            $error = oci_error($stmt);
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar: " . addslashes($error['message']) . "',
                    confirmButtonColor: '#2c3e50'
                });
            </script>";
        }
    } else {
        $mensaje_error = implode("\\n", $errores);
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Errores de validación',
                text: '$mensaje_error',
                confirmButtonColor: '#2c3e50'
            });
        </script>";
    }
}
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-edit"></i> Editar Cliente: <?php echo htmlspecialchars($cliente['NOMBRE']); ?>
    </div>
    <div class="card-body">
        <form method="POST" onsubmit="return validarFormulario(event)">
            <input type="hidden" name="id" value="<?php echo $cliente['ID_CLIENTE']; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">
                        <i class="fas fa-user"></i> Nombre completo *
                    </label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($cliente['NOMBRE']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">
                        <i class="fas fa-phone"></i> Teléfono *
                    </label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required 
                           value="<?php echo htmlspecialchars($cliente['TELEFONO']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="correo" class="form-label">
                        <i class="fas fa-envelope"></i> Correo electrónico *
                    </label>
                    <input type="email" class="form-control" id="correo" name="correo" required 
                           value="<?php echo htmlspecialchars($cliente['CORREO']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Dirección *
                    </label>
                    <input type="text" class="form-control" id="direccion" name="direccion" required 
                           value="<?php echo htmlspecialchars($cliente['DIRECCION']); ?>">
                </div>
            </div>
            
            <hr>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Cliente
                </button>
                <a href="clientes.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function validarFormulario(event) {
    event.preventDefault();
    
    var nombre = document.getElementById('nombre').value.trim();
    var telefono = document.getElementById('telefono').value.trim();
    var correo = document.getElementById('correo').value.trim();
    var direccion = document.getElementById('direccion').value.trim();
    
    // Validaciones
    if (nombre === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese el nombre del cliente',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (telefono === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese el teléfono',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (correo === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese el correo electrónico',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) {
        Swal.fire({
            icon: 'warning',
            title: 'Correo inválido',
            text: 'Por favor ingrese un correo electrónico válido',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (direccion === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese la dirección',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    event.target.submit();
    return true;
}
</script>

<?php
$db->close();
include __DIR__ . '/../../includes/footer.php';
?>