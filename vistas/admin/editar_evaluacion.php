<?php
require_once 'layout/header.php';
require_once '../../config/database.php'; // Cambiado de conexion.php a database.php

$id_evaluacion = $_GET['id'] ?? 0;

if ($id_evaluacion == 0) {
    header("Location: gestionar_evaluaciones.php");
    exit();
}

// Fetch evaluation data using select_one from the abstraction layer
$evaluacion = select_one("SELECT * FROM evaluaciones WHERE id = ?", "i", [$id_evaluacion]);

if (!$evaluacion) {
    header("Location: gestionar_evaluaciones.php");
    exit();
}

// Fetch active courses for the dropdown
$query_cursos_dropdown = "SELECT id, nombre_curso FROM cursos WHERE estado = 'activo' ORDER BY nombre_curso ASC";
$cursos_dropdown = select_all($query_cursos_dropdown); // Usando select_all() de database.php
?>

<h1 class="mb-4">Editar Evaluación</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos de la Evaluación</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/evaluacion_controller.php" method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id_evaluacion" value="<?php echo $evaluacion['id']; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_curso" class="form-label">Curso</label>
                    <select class="form-select" id="id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php foreach($cursos_dropdown as $curso): // Cambiado de while a foreach ?>
                            <option value="<?php echo $curso['id']; ?>" <?php echo ($curso['id'] == $evaluacion['id_curso']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($curso['nombre_curso']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nombre_evaluacion" class="form-label">Nombre de la Evaluación</label>
                    <input type="text" class="form-control" id="nombre_evaluacion" name="nombre_evaluacion" value="<?php echo htmlspecialchars($evaluacion['nombre_evaluacion']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="porcentaje" class="form-label">Porcentaje (%)</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="porcentaje" name="porcentaje" value="<?php echo htmlspecialchars($evaluacion['porcentaje']); ?>" required>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Actualizar Evaluación</button>
                <a href="gestionar_evaluaciones.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
// $conexion->close(); // Eliminado
require_once 'layout/footer.php';
?>
