<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/tarea_controller.php';

// Get the logged-in student's user ID
$id_user_estudiante = $_SESSION['user_id'];

// Fetch the student's ID from the estudiantes table
$stmt_estudiante_id = $conexion->prepare("SELECT id FROM estudiantes WHERE id_user = ?");
$stmt_estudiante_id->bind_param("i", $id_user_estudiante);
$stmt_estudiante_id->execute();
$resultado_estudiante_id = $stmt_estudiante_id->get_result();
$estudiante_data = $resultado_estudiante_id->fetch_assoc();
$id_estudiante = $estudiante_data['id'] ?? 0;
$stmt_estudiante_id->close();

if ($id_estudiante == 0) {
    echo "<div class='alert alert-danger'>No se pudo encontrar el perfil del estudiante.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Store student ID in session for controller use
$_SESSION['id_estudiante'] = $id_estudiante;

// Fetch all tasks for the student's assigned courses
$query_tareas = "SELECT
                    t.id AS id_tarea,
                    t.titulo AS tarea_titulo,
                    t.descripcion AS tarea_descripcion,
                    t.fecha_entrega,
                    t.tipo_entrega,
                    t.estado AS tarea_estado,
                    c.nombre_curso,
                    dc.periodo_academico,
                    et.estado AS entrega_estado,
                    et.calificacion
                 FROM tareas t
                 JOIN docente_curso dc ON t.id_asignacion = dc.id
                 JOIN cursos c ON dc.id_curso = c.id
                 JOIN matriculas m ON c.id = m.id_curso AND dc.periodo_academico = m.periodo_academico
                 LEFT JOIN entregas_tarea et ON t.id = et.id_tarea AND m.id_estudiante = et.id_estudiante
                 WHERE m.id_estudiante = ? AND m.estado = 'matriculado'
                 ORDER BY t.fecha_entrega ASC";

$stmt_tareas = $conexion->prepare($query_tareas);
$stmt_tareas->bind_param("i", $id_estudiante);
$stmt_tareas->execute();
$resultado_tareas = $stmt_tareas->get_result();
$tareas_estudiante = [];
while ($tarea = $resultado_tareas->fetch_assoc()) {
    $tareas_estudiante[] = $tarea;
}
$stmt_tareas->close();

?>

<h1 class="mb-4">Mis Tareas</h1>
<p>Aquí puedes ver todas las tareas asignadas a tus cursos, su estado y calificaciones.</p>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Listado de Tareas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="misTareasTable" class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Curso</th>
                        <th>Tarea</th>
                        <th>Fecha de Entrega</th>
                        <th>Estado de Tarea</th>
                        <th>Estado de Entrega</th>
                        <th>Calificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tareas_estudiante)): ?>
                        <?php foreach($tareas_estudiante as $tarea): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tarea['nombre_curso'] . ' (' . $tarea['periodo_academico'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($tarea['tarea_titulo']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['fecha_entrega']))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($tarea['tarea_estado'])); ?></td>
                                <td>
                                    <?php
                                        $entrega_estado = $tarea['entrega_estado'] ?? 'pendiente';
                                        $badge_class = '';
                                        switch ($entrega_estado) {
                                            case 'pendiente': $badge_class = 'bg-secondary'; break;
                                            case 'entregado': $badge_class = 'bg-primary'; break;
                                            case 'calificado': $badge_class = 'bg-success'; break;
                                            case 'retrasado': $badge_class = 'bg-danger'; break;
                                            default: $badge_class = 'bg-secondary'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($entrega_estado)); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($tarea['calificacion'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="detalle_tarea.php?id_tarea=<?php echo $tarea['id_tarea']; ?>" class="btn btn-sm btn-info" title="Ver Detalle y Entregar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No tienes tareas asignadas actualmente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($tareas_estudiante)): ?>
<script>
$(document).ready(function() {
    $('#misTareasTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});
</script>
<?php endif; ?>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
