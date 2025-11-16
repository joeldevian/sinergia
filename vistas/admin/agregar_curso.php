<?php
require_once 'layout/header.php';
require_once '../../config/database.php'; // Cambiado de conexion.php a database.php

// Fetch careers for the dropdown
$query_carreras = "SELECT id, nombre_carrera FROM carreras WHERE estado = 'activa' ORDER BY nombre_carrera ASC";
$carreras = select_all($query_carreras); // Usando select_all() de database.php
?>

<h1 class="mb-4">Agregar Nuevo Curso</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos del Curso</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/curso_controller.php" method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo_curso" class="form-label">Código del Curso</label>
                    <input type="text" class="form-control" id="codigo_curso" name="codigo_curso" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nombre_curso" class="form-label">Nombre del Curso</label>
                    <input type="text" class="form-control" id="nombre_curso" name="nombre_curso" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="creditos" class="form-label">Créditos</label>
                    <input type="number" class="form-control" id="creditos" name="creditos" min="1" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="horas_semanales" class="form-label">Horas Semanales</label>
                    <input type="number" class="form-control" id="horas_semanales" name="horas_semanales" min="1" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="id_carrera" class="form-label">Carrera</label>
                    <select class="form-select" id="id_carrera" name="id_carrera" required>
                        <option value="">Seleccione una carrera</option>
                        <?php foreach($carreras as $carrera): // Cambiado de while a foreach ?>
                            <option value="<?php echo $carrera['id']; ?>"><?php echo htmlspecialchars($carrera['nombre_carrera']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="ciclo" class="form-label">Ciclo</label>
                    <input type="text" class="form-control" id="ciclo" name="ciclo">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="obligatorio">Obligatorio</option>
                        <option value="electivo">Electivo</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Guardar Curso</button>
                <a href="gestionar_cursos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
// $conexion->close(); // Eliminado, ya que la función select_all() cierra el statement
require_once 'layout/footer.php';
?>
