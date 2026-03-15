<?php
session_start();
require_once __DIR__ . '/Conexion/conexion.php';

// Si ya está logueado, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Buscar usuario por correo
    $query = "SELECT * FROM MUEBLERIA.USUARIO WHERE CORREO = :correo AND ESTADO = 'ACTIVO'";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':correo', $correo);
    oci_execute($stmt);
    $usuario = oci_fetch_assoc($stmt);
    
    if ($usuario && $clave == $usuario['CLAVE']) { // En producción deberías usar password_verify
        // Iniciar sesión
        $_SESSION['usuario_id'] = $usuario['ID_USUARIO'];
        $_SESSION['usuario_nombre'] = $usuario['NOMBRE'];
        $_SESSION['usuario_correo'] = $usuario['CORREO'];
        $_SESSION['usuario_rol'] = $usuario['ID_ROL'];
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos';
    }
    
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mueblería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card h2 i {
            color: #e67e22;
            margin-right: 10px;
        }
        .btn-login {
            background-color: #2c3e50;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #34495e;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>
            <i class="fas fa-chair"></i> Mueblería
        </h2>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">
                    <i class="fas fa-envelope"></i> Correo electrónico
                </label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            
            <div class="mb-3">
                <label for="clave" class="form-label">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                Usuarios de prueba: carlos.ramirez@gmail.com / cram123
            </small>
        </div>
    </div>
</body>
</html>