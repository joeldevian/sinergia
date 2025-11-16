<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

$id_estudiante = $_GET['id'] ?? 0;

if ($id_estudiante == 0) {
    // Redirect or show error if no ID is provided
    header("Location: gestionar_estudiantes.php");
    exit();
}

// Fetch student data
$stmt = $conexion->prepare("SELECT * FROM estudiantes WHERE id = ?");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$resultado = $stmt->get_result();
$estudiante = $resultado->fetch_assoc();

if (!$estudiante) {
    // Redirect or show error if student not found
    header("Location: gestionar_estudiantes.php");
    exit();
}
$stmt->close();
?>

<h1 class="mb-4">Editar Estudiante</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos del Estudiante</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/estudiante_controller.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_estudiante" value="<?php echo $estudiante['id']; ?>">
            <input type="hidden" name="id_user" value="<?php echo $estudiante['id_user']; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($estudiante['nombres']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese los nombres.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($estudiante['apellido_paterno']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese el apellido paterno.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($estudiante['apellido_materno']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" id="dni" name="dni" value="<?php echo htmlspecialchars($estudiante['dni']); ?>" required pattern="[0-9]{8}" title="El DNI debe contener 8 dígitos.">
                    <div class="invalid-feedback">
                        Por favor, ingrese un DNI válido de 8 dígitos.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="codigo_estudiante" class="form-label">Código de Estudiante</label>
                    <input type="text" class="form-control" id="codigo_estudiante" name="codigo_estudiante" value="<?php echo htmlspecialchars($estudiante['codigo_estudiante']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese el código de estudiante.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($estudiante['email']); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese un email válido.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($estudiante['telefono']); ?>" pattern="[0-9]{9,15}">
                    <div class="invalid-feedback">
                        Por favor, ingrese un número de teléfono válido.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($estudiante['fecha_nacimiento']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select class="form-select" id="sexo" name="sexo">
                        <option value="O" <?php echo $estudiante['sexo'] == 'O' ? 'selected' : ''; ?>>Otro</option>
                        <option value="M" <?php echo $estudiante['sexo'] == 'M' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="F" <?php echo $estudiante['sexo'] == 'F' ? 'selected' : ''; ?>>Femenino</option>
                    </select>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($estudiante['direccion']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="activo" <?php echo $estudiante['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $estudiante['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="egresado" <?php echo $estudiante['estado'] == 'egresado' ? 'selected' : ''; ?>>Egresado</option>
                        <option value="desertor" <?php echo $estudiante['estado'] == 'desertor' ? 'selected' : ''; ?>>Desertor</option>
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
                <button type="submit" class="btn btn-primary">Actualizar Estudiante</button>
                <a href="gestionar_estudiantes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>