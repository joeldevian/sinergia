<?php
$pageTitle = "Mi Perfil";
require_once 'layout/header.php';
require_once '../../config/conexion.php'; // Needed to fetch user data

// Fetch current user data
$id_user = $_SESSION['user_id'];
$stmt_user = $conexion->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt_user->bind_param("i", $id_user);
$stmt_user->execute();
$resultado_user = $stmt_user->get_result();
$user_data = $resultado_user->fetch_assoc();
$stmt_user->close();

// Ensure email is not null for display, though it might be in DB
$user_email = $user_data['email'] ?? '';

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

<div class="row justify-content-center">
    <!-- Edit User Info Card -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Editar Información de Usuario</h5>
            </div>
            <div class="card-body">
                <form action="../../controladores/perfil_controller.php" method="POST">
                    <input type="hidden" name="accion" value="actualizar_perfil">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Información</button>
                </form>
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
$conexion->close(); // Close the connection opened at the top
require_once 'layout/footer.php';
?>