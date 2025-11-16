<?php
$pageTitle = "Mi Perfil";
require_once 'layout/header.php';
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
require_once 'layout/footer.php';
?>
