<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Get the logged-in student's user ID
$id_user_estudiante = $_SESSION['user_id'];

// Fetch the student's full details from the estudiantes table
$stmt = $conexion->prepare("SELECT * FROM estudiantes WHERE id_user = ?");
$stmt->bind_param("i", $id_user_estudiante);
$stmt->execute();
$resultado = $stmt->get_result();
$estudiante = $resultado->fetch_assoc();
$stmt->close();

if (!$estudiante) {
    // This should not happen if the user is logged in correctly
    echo "<div class='alert alert-danger'>Error: No se pudieron encontrar los datos del estudiante.</div>";
    require_once 'layout/footer.php';
    exit();
}
?>

<h1 class="mb-4">Mi Perfil</h1>

<?php
// Display success or error messages from the controller
if (isset($_SESSION['mensaje_perfil'])) {
    echo '<div class="alert alert-' . $_SESSION['mensaje_perfil_tipo'] . ' alert-dismissible fade show" role="alert">';
    echo $_SESSION['mensaje_perfil'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['mensaje_perfil']);
    unset($_SESSION['mensaje_perfil_tipo']);
}
?>

<div class="row">
    <!-- Personal Data Card -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mis Datos Personales</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Nombres:</strong> <?php echo htmlspecialchars($estudiante['nombres']); ?></li>
                    <li class="list-group-item"><strong>Apellidos:</strong> <?php echo htmlspecialchars($estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']); ?></li>
                    <li class="list-group-item"><strong>Código:</strong> <?php echo htmlspecialchars($estudiante['codigo_estudiante']); ?></li>
                    <li class="list-group-item"><strong>DNI:</strong> <?php echo htmlspecialchars($estudiante['dni']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($estudiante['email']); ?></li>
                    <li class="list-group-item"><strong>Teléfono:</strong> <?php echo htmlspecialchars($estudiante['telefono'] ?? 'No registrado'); ?></li>
                    <li class="list-group-item"><strong>Dirección:</strong> <?php echo htmlspecialchars($estudiante['direccion'] ?? 'No registrada'); ?></li>
                    <li class="list-group-item"><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($estudiante['fecha_nacimiento'] ?? 'No registrada'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
            </div>
            <div class="card-body">
                <form action="../../controladores/perfil_controller.php" method="POST">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
