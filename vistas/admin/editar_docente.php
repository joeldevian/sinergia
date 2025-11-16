<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

$id_docente = $_GET['id'] ?? 0;

if ($id_docente == 0) {
    // Redirect or show error if no ID is provided
    header("Location: gestionar_docentes.php");
    exit();
}

// Fetch teacher data
$stmt = $conexion->prepare("SELECT * FROM docentes WHERE id = ?");
$stmt->bind_param("i", $id_docente);
$stmt->execute();
$resultado = $stmt->get_result();
$docente = $resultado->fetch_assoc();

if (!$docente) {
    // Redirect or show error if teacher not found
    header("Location: gestionar_docentes.php");
    exit();
}
$stmt->close();
?>

<h1 class="mb-4">Editar Docente</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos del Docente</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/docente_controller.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_docente" value="<?php echo htmlspecialchars($docente['id']); ?>">
            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($docente['id_user']); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($docente['nombres']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese los nombres.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($docente['apellido_paterno']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese el apellido paterno.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($docente['apellido_materno']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" id="dni" name="dni" value="<?php echo htmlspecialchars($docente['dni']); ?>" required pattern="[0-9]{8}" title="El DNI debe contener 8 dígitos.">
                    <div class="invalid-feedback">
                        Por favor, ingrese un DNI válido de 8 dígitos.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="codigo_docente" class="form-label">Código de Docente</label>
                    <input type="text" class="form-control" id="codigo_docente" name="codigo_docente" value="<?php echo htmlspecialchars($docente['codigo_docente']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese el código de docente.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($docente['email']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese un email válido.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($docente['telefono']); ?>" pattern="[0-9]{9,15}">
                    <div class="invalid-feedback">
                        Por favor, ingrese un número de teléfono válido.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="especialidad" class="form-label">Especialidad</label>
                    <input type="text" class="form-control" id="especialidad" name="especialidad" value="<?php echo htmlspecialchars($docente['especialidad']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="activo" <?php echo $docente['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $docente['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="suspendido" <?php echo $docente['estado'] == 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                    </select>
                </div>
            </div>
            <hr>
            <h5 class="mt-4 mb-3">Cambiar Contraseña (Opcional)</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6">
                    <div class="invalid-feedback">
                        La contraseña debe tener al menos 6 caracteres.
                    </div>
                    <div class="form-text">Dejar en blanco para no cambiar la contraseña.</div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Actualizar Docente</button>
                <a href="gestionar_docentes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>