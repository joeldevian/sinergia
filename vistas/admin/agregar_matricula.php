<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch active students for the dropdown
$query_estudiantes = "SELECT id, CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre_completo ASC";
$resultado_estudiantes = $conexion->query($query_estudiantes);

// Fetch active courses for the dropdown
$query_cursos = "SELECT id, nombre_curso FROM cursos WHERE estado = 'activo' ORDER BY nombre_curso ASC";
$resultado_cursos = $conexion->query($query_cursos);
?>

<h1 class="mb-4">Agregar Nueva Matrícula</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos de la Matrícula</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/matricula_controller.php" method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_estudiante" class="form-label">Estudiante</label>
                    <select class="form-select" id="id_estudiante" name="id_estudiante" required>
                        <option value="">Seleccione un estudiante</option>
                        <?php while($estudiante = $resultado_estudiantes->fetch_assoc()): ?>
                            <option value="<?php echo $estudiante['id']; ?>"><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="id_curso" class="form-label">Curso</label>
                    <select class="form-select" id="id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php while($curso = $resultado_cursos->fetch_assoc()): ?>
                            <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre_curso']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="periodo_academico" class="form-label">Periodo Académico</label>
                    <input type="text" class="form-control" id="periodo_academico" name="periodo_academico" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_matricula" class="form-label">Fecha de Matrícula</label>
                    <input type="date" class="form-control" id="fecha_matricula" name="fecha_matricula" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Guardar Matrícula</button>
                <a href="gestionar_matriculas.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
