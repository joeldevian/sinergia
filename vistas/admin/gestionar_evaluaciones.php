<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch active courses for the dropdown
$query_cursos_dropdown = "SELECT id, nombre_curso FROM cursos WHERE estado = 'activo' ORDER BY nombre_curso ASC";
$resultado_cursos_dropdown = $conexion->query($query_cursos_dropdown);

// Fetch existing evaluations, grouped by course
$query_evaluaciones = "SELECT 
                            e.id, e.nombre_evaluacion, e.porcentaje, 
                            c.nombre_curso, c.codigo_curso
                         FROM evaluaciones e
                         JOIN cursos c ON e.id_curso = c.id
                         ORDER BY c.nombre_curso ASC, e.nombre_evaluacion ASC";
$resultado_evaluaciones = $conexion->query($query_evaluaciones);

$evaluaciones_por_curso = [];
if ($resultado_evaluaciones->num_rows > 0) {
    while ($evaluacion = $resultado_evaluaciones->fetch_assoc()) {
        $evaluaciones_por_curso[$evaluacion['nombre_curso']][] = $evaluacion;
    }
}
?>

<h1 class="mb-4">Configuración de Evaluaciones</h1>

<?php
if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-' . $_SESSION['mensaje_tipo'] . ' alert-dismissible fade show" role="alert">';
    echo $_SESSION['mensaje'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
}
?>

<!-- Form to add new evaluation -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Nueva Configuración de Evaluación</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/evaluacion_controller.php" method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="id_curso" class="form-label">Curso</label>
                    <select class="form-select" id="id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php while($curso = $resultado_cursos_dropdown->fetch_assoc()): ?>
                            <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre_curso']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="nombre_evaluacion" class="form-label">Nombre de la Evaluación</label>
                    <input type="text" class="form-control" id="nombre_evaluacion" name="nombre_evaluacion" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="porcentaje" class="form-label">Porcentaje (%)</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="porcentaje" name="porcentaje" required>
                </div>
                <div class="col-md-1 mb-3 d-grid">
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table of existing evaluations -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Configuraciones de Evaluaciones Actuales</h5>
    </div>
    <div class="card-body">
        <?php if (empty($evaluaciones_por_curso)): ?>
            <p class="text-center">No hay evaluaciones registradas.</p>
        <?php else: ?>
            <?php foreach ($evaluaciones_por_curso as $nombre_curso => $evaluaciones): ?>
                <div class="mb-4">
                    <h4>Curso: <?php echo htmlspecialchars($nombre_curso); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover datatable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre Evaluación</th>
                                    <th>Porcentaje</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluaciones as $eval): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($eval['nombre_evaluacion']); ?></td>
                                        <td><?php echo htmlspecialchars($eval['porcentaje']); ?>%</td>
                                        <td>
                                            <a href="editar_evaluacion.php?id=<?php echo $eval['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../../controladores/evaluacion_controller.php?accion=eliminar&id=<?php echo $eval['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta evaluación? Esto también eliminará las notas asociadas.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
