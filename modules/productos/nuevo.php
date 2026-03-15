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

// Obtener categorías
$query_cat = "SELECT * FROM MUEBLERIA.CATEGORIA ORDER BY NOMBRE_CATEGORIA";
$stmt_cat = oci_parse($conn, $query_cat);
oci_execute($stmt_cat);

// Obtener proveedores
$query_prov = "SELECT * FROM MUEBLERIA.PROVEEDOR ORDER BY NOMBRE";
$stmt_prov = oci_parse($conn, $query_prov);
oci_execute($stmt_prov);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $madera = $_POST['madera'];
    $medidas = $_POST['medidas'];
    $foto_url = $_POST['foto_url'];  // Nuevo campo
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor'];
    $estado = $_POST['estado'];
    
    // Validar que la URL de imagen no esté vacía
    if (empty($foto_url)) {
        $foto_url = 'https://via.placeholder.com/300x300?text=Sin+Imagen';
    }
    
    // Obtener siguiente ID
    $query_id = "SELECT NVL(MAX(ID_PRODUCTO), 0) + 1 as ID FROM MUEBLERIA.PRODUCTO";
    $stmt_id = oci_parse($conn, $query_id);
    oci_execute($stmt_id);
    $row_id = oci_fetch_assoc($stmt_id);
    $nuevo_id = $row_id['ID'];
    
    // Insertar producto
    $query = "INSERT INTO MUEBLERIA.PRODUCTO 
              (ID_PRODUCTO, NOMBRE, DESCRIPCION, PRECIO, MADERA, MEDIDAS, FOTO_URL, ESTADO, ID_CATEGORIA, ID_PROVEEDOR)
              VALUES 
              (:id, :nombre, :descripcion, :precio, :madera, :medidas, :foto_url, :estado, :id_categoria, :id_proveedor)";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':id', $nuevo_id);
    oci_bind_by_name($stmt, ':nombre', $nombre);
    oci_bind_by_name($stmt, ':descripcion', $descripcion);
    oci_bind_by_name($stmt, ':precio', $precio);
    oci_bind_by_name($stmt, ':madera', $madera);
    oci_bind_by_name($stmt, ':medidas', $medidas);
    oci_bind_by_name($stmt, ':foto_url', $foto_url);
    oci_bind_by_name($stmt, ':estado', $estado);
    oci_bind_by_name($stmt, ':id_categoria', $id_categoria);
    oci_bind_by_name($stmt, ':id_proveedor', $id_proveedor);
    
    if (oci_execute($stmt)) {
        // Alerta de éxito con SweetAlert2
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Producto guardado!',
                text: 'El producto \"$nombre\" ha sido creado exitosamente',
                confirmButtonColor: '#2c3e50',
                confirmButtonText: 'Ver productos'
            }).then((result) => {
                window.location.href = 'productos.php';
            });
        </script>";
    } else {
        $error = oci_error($stmt);
        // Alerta de error con SweetAlert2
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error al guardar: " . addslashes($error['message']) . "',
                confirmButtonColor: '#2c3e50'
            });
        </script>";
    }
}
?>

<style>
/* Estilos adicionales para el formulario */
.image-preview {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px dashed #ddd;
    padding: 5px;
    margin-top: 10px;
    display: none;
}

.image-preview.show {
    display: block;
}

.image-preview-container {
    text-align: center;
    margin-bottom: 20px;
}

.help-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 5px;
}
</style>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> Nuevo Producto
    </div>
    <div class="card-body">
        <form method="POST" onsubmit="return validarFormulario(event)">
            <div class="row">
                <!-- Columna izquierda - Imagen -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-image"></i> Imagen del Producto
                        </div>
                        <div class="card-body text-center">
                            <div class="image-preview-container">
                                <img id="preview" class="image-preview show" 
                                     src="https://via.placeholder.com/300x300?text=Previsualización" 
                                     alt="Vista previa">
                            </div>
                            <div class="mb-3">
                                <label for="foto_url" class="form-label">URL de la imagen</label>
                                <input type="url" class="form-control" id="foto_url" name="foto_url" 
                                       placeholder="https://ejemplo.com/imagen.jpg"
                                       onchange="actualizarPreview(this.value)">
                                <div class="help-text">
                                    <i class="fas fa-info-circle"></i> 
                                    Puedes usar imágenes de Freepik, Pexels, Unsplash, etc.
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb"></i>
                                    Ejemplo: https://img.freepik.com/...
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Columna derecha - Datos del producto -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-tag"></i> Nombre *
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required 
                                   placeholder="Ej: Silla Clásica">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="precio" class="form-label">
                                <i class="fas fa-dollar-sign"></i> Precio *
                            </label>
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   step="0.01" required placeholder="0.00">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Descripción del producto"></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="madera" class="form-label">
                                <i class="fas fa-tree"></i> Madera
                            </label>
                            <input type="text" class="form-control" id="madera" name="madera" 
                                   placeholder="Ej: Roble, Cedro, Pino...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="medidas" class="form-label">
                                <i class="fas fa-ruler"></i> Medidas
                            </label>
                            <input type="text" class="form-control" id="medidas" name="medidas" 
                                   placeholder="Ej: 45x45x90">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_categoria" class="form-label">
                                <i class="fas fa-list"></i> Categoría *
                            </label>
                            <select class="form-control" id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccione...</option>
                                <?php 
                                // Reiniciar el puntero del statement
                                oci_execute($stmt_cat);
                                while ($cat = oci_fetch_assoc($stmt_cat)): 
                                ?>
                                <option value="<?php echo $cat['ID_CATEGORIA']; ?>">
                                    <?php echo $cat['NOMBRE_CATEGORIA']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_proveedor" class="form-label">
                                <i class="fas fa-truck"></i> Proveedor *
                            </label>
                            <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                                <option value="">Seleccione...</option>
                                <?php 
                                // Reiniciar el puntero del statement
                                oci_execute($stmt_prov);
                                while ($prov = oci_fetch_assoc($stmt_prov)): 
                                ?>
                                <option value="<?php echo $prov['ID_PROVEEDOR']; ?>">
                                    <?php echo $prov['NOMBRE']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">
                                <i class="fas fa-circle"></i> Estado
                            </label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
                <a href="productos.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Función para actualizar la vista previa de la imagen
function actualizarPreview(url) {
    var preview = document.getElementById('preview');
    if (url.trim() !== '') {
        preview.src = url;
        preview.classList.add('show');
        // Manejar error de carga
        preview.onerror = function() {
            this.src = 'https://via.placeholder.com/300x300?text=Error+al+cargar';
        };
    } else {
        preview.src = 'https://via.placeholder.com/300x300?text=Previsualización';
    }
}

// Función para validar el formulario
function validarFormulario(event) {
    event.preventDefault(); 
    
    // Obtener valores
    var nombre = document.getElementById('nombre').value;
    var precio = document.getElementById('precio').value;
    var id_categoria = document.getElementById('id_categoria').value;
    var id_proveedor = document.getElementById('id_proveedor').value;
    var foto_url = document.getElementById('foto_url').value;
    
    // Validaciones
    if (nombre.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese el nombre del producto',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (precio.trim() === '' || parseFloat(precio) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor ingrese un precio válido',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (id_categoria === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione una categoría',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    if (id_proveedor === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Por favor seleccione un proveedor',
            confirmButtonColor: '#2c3e50'
        });
        return false;
    }
    
    // Validar URL de imagen 
    if (foto_url.trim() !== '') {
        
        var urlPattern = /^(http|https):\/\/[^ "]+$/;
        if (!urlPattern.test(foto_url)) {
            Swal.fire({
                icon: 'warning',
                title: 'URL no válida',
                text: 'Por favor ingrese una URL válida para la imagen',
                confirmButtonColor: '#2c3e50'
            });
            return false;
        }
    }
    
    // Si todo está bien, enviar el formulario
    event.target.submit();
    return true;
}

// Inicializar la vista previa si hay una URL por defecto
document.addEventListener('DOMContentLoaded', function() {
    var fotoUrl = document.getElementById('foto_url');
    if (fotoUrl.value) {
        actualizarPreview(fotoUrl.value);
    }
});
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>