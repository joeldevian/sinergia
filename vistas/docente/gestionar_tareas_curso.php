<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/tarea_controller.php';

$id_asignacion = $_GET['id_asignacion'] ?? 0;

if ($id_asignacion == 0) {
    echo "<div class='alert alert-danger'>ID de asignación no proporcionado.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch assignment and course details
$query_asignacion = "SELECT
                        a.id AS id_asignacion,
                        c.nombre_curso,
                        c.codigo_curso,
                        d.nombres AS docente_nombres,
                        d.apellido_paterno AS docente_apellido_paterno,
                        d.apellido_materno AS docente_apellido_materno,
                        a.periodo_academico
                     FROM docente_curso a
                     JOIN cursos c ON a.id_curso = c.id
                     JOIN docentes d ON a.id_docente = d.id
                     WHERE a.id = ?";
$stmt_asignacion = $conexion->prepare($query_asignacion);
$stmt_asignacion->bind_param("i", $id_asignacion);
$stmt_asignacion->execute();
$resultado_asignacion = $stmt_asignacion->get_result();
$asignacion_data = $resultado_asignacion->fetch_assoc();
$stmt_asignacion->close();

if (!$asignacion_data) {
    echo "<div class='alert alert-danger'>Asignación no encontrada.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch existing tasks
$tareas = TareaController::obtenerTareasPorAsignacion($conexion, $id_asignacion);

?>

<h1 class="mb-4">Gestionar Tareas para: <?php echo htmlspecialchars($asignacion_data['nombre_curso']); ?> (<?php echo htmlspecialchars($asignacion_data['periodo_academico']); ?>)</h1>
<p>Docente: <?php echo htmlspecialchars($asignacion_data['docente_nombres'] . ' ' . $asignacion_data['docente_apellido_paterno'] . ' ' . $asignacion_data['docente_apellido_materno']); ?></p>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Crear Nueva Tarea</h5>
            </div>
            <div class="card-body">
                <form action="../../controladores/tarea_controller.php" method="POST">
                    <input type="hidden" name="action" value="crear_tarea">
                    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título de la Tarea</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                        <input type="datetime-local" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_entrega" class="form-label">Tipo de Entrega</label>
                        <select class="form-select" id="tipo_entrega" name="tipo_entrega" required>
                            <option value="archivo">Archivo</option>
                            <option value="texto">Texto en línea</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Crear Tarea</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Tareas Existentes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="misTareasCursoTable" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Fecha de Entrega</th>
                        <th>Tipo de Entrega</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tareas)): ?>
                        <?php foreach($tareas as $tarea): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tarea['titulo']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['fecha_entrega']))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($tarea['tipo_entrega'])); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($tarea['estado'])); ?></td>
                                <td>
                                    <a href="editar_tarea.php?id_tarea=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-warning" title="Editar Tarea">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="ver_entregas_tarea.php?id_tarea=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-info" title="Ver Entregas">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="../../controladores/tarea_controller.php" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Está seguro de eliminar esta tarea? Esto también eliminará todas las entregas asociadas.');">
                                        <input type="hidden" name="action" value="eliminar_tarea">
                                        <input type="hidden" name="id_tarea" value="<?php echo $tarea['id']; ?>">
                                        <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Tarea">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay tareas creadas para este curso.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($tareas)): ?>
<script>
$(document).ready(function() {
    $('#misTareasCursoTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});
</script>
<?php endif; ?>

<div class="mt-4">
    <a href="gestionar_tareas.php" class="btn btn-secondary">Volver a Mis Cursos</a>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
