<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/tarea_controller.php';

$id_tarea = $_GET['id_tarea'] ?? 0;

if ($id_tarea == 0) {
    echo "<div class='alert alert-danger'>ID de tarea no proporcionado.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch task details
$tarea = TareaController::obtenerTareaPorId($conexion, $id_tarea);

if (!$tarea) {
    echo "<div class='alert alert-danger'>Tarea no encontrada.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch assignment details to get course name
$query_asignacion = "SELECT
                        c.nombre_curso,
                        c.codigo_curso,
                        dc.periodo_academico
                     FROM docente_curso dc
                     JOIN cursos c ON dc.id_curso = c.id
                     WHERE dc.id = ?";
$stmt_asignacion = $conexion->prepare($query_asignacion);
$stmt_asignacion->bind_param("i", $tarea['id_asignacion']);
$stmt_asignacion->execute();
$resultado_asignacion = $stmt_asignacion->get_result();
$asignacion_data = $resultado_asignacion->fetch_assoc();
$stmt_asignacion->close();

// Fetch all submissions for this task
$entregas = TareaController::obtenerEntregasPorTarea($conexion, $id_tarea);

?>

<h1 class="mb-4">Entregas para la Tarea: <?php echo htmlspecialchars($tarea['titulo']); ?></h1>
<p>Curso: <?php echo htmlspecialchars($asignacion_data['nombre_curso'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($asignacion_data['periodo_academico'] ?? 'N/A'); ?>)</p>
<p>Fecha de Entrega: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['fecha_entrega']))); ?></p>
<p>Descripción: <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?></p>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Listado de Entregas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Fecha de Entrega</th>
                        <th>Estado</th>
                        <th>Entrega</th>
                        <th>Calificación</th>
                        <th>Comentarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($entregas)): ?>
                        <?php foreach($entregas as $entrega): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entrega['nombres'] . ' ' . $entrega['apellido_paterno'] . ' ' . $entrega['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($entrega['fecha_entrega']))); ?></td>
                                <td>
                                    <?php
                                        $badge_class = '';
                                        switch ($entrega['estado']) {
                                            case 'pendiente': $badge_class = 'bg-secondary'; break;
                                            case 'entregado': $badge_class = 'bg-primary'; break;
                                            case 'calificado': $badge_class = 'bg-success'; break;
                                            case 'retrasado': $badge_class = 'bg-danger'; break;
                                            default: $badge_class = 'bg-secondary'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($entrega['estado'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($tarea['tipo_entrega'] == 'archivo' && $entrega['ruta_archivo']): ?>
                                        <a href="../../uploads/entregas_tarea/<?php echo htmlspecialchars($entrega['ruta_archivo']); ?>" target="_blank" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-download"></i> Archivo
                                        </a>
                                    <?php elseif ($tarea['tipo_entrega'] == 'texto' && $entrega['texto_entrega']): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modalTextoEntrega<?php echo $entrega['id']; ?>">
                                            <i class="fas fa-file-alt"></i> Ver Texto
                                        </button>
                                        <!-- Modal para ver texto de entrega -->
                                        <div class="modal fade" id="modalTextoEntrega<?php echo $entrega['id']; ?>" tabindex="-1" aria-labelledby="modalTextoEntregaLabel<?php echo $entrega['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalTextoEntregaLabel<?php echo $entrega['id']; ?>">Entrega de <?php echo htmlspecialchars($entrega['nombres'] . ' ' . $entrega['apellido_paterno']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo nl2br(htmlspecialchars($entrega['texto_entrega'])); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        No entregado
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($entrega['calificacion'] ?? 'N/A'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($entrega['comentarios_docente'] ?? 'Sin comentarios')); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCalificarEntrega<?php echo $entrega['id']; ?>" title="Calificar">
                                        <i class="fas fa-marker"></i>
                                    </button>

                                    <!-- Modal para calificar entrega -->
                                    <div class="modal fade" id="modalCalificarEntrega<?php echo $entrega['id']; ?>" tabindex="-1" aria-labelledby="modalCalificarEntregaLabel<?php echo $entrega['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="../../controladores/tarea_controller.php" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalCalificarEntregaLabel<?php echo $entrega['id']; ?>">Calificar Entrega de <?php echo htmlspecialchars($entrega['nombres'] . ' ' . $entrega['apellido_paterno']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="calificar_entrega">
                                                        <input type="hidden" name="id_entrega" value="<?php echo $entrega['id']; ?>">
                                                        <input type="hidden" name="id_tarea" value="<?php echo $id_tarea; ?>">
                                                        <div class="mb-3">
                                                            <label for="calificacion<?php echo $entrega['id']; ?>" class="form-label">Calificación</label>
                                                            <input type="number" step="0.01" min="0" max="20" class="form-control" id="calificacion<?php echo $entrega['id']; ?>" name="calificacion" value="<?php echo htmlspecialchars($entrega['calificacion'] ?? ''); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="comentarios_docente<?php echo $entrega['id']; ?>" class="form-label">Comentarios</label>
                                                            <textarea class="form-control" id="comentarios_docente<?php echo $entrega['id']; ?>" name="comentarios_docente" rows="3"><?php echo htmlspecialchars($entrega['comentarios_docente'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        <button type="submit" class="btn btn-success">Guardar Calificación</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay entregas para esta tarea aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="gestionar_tareas_curso.php?id_asignacion=<?php echo $tarea['id_asignacion']; ?>" class="btn btn-secondary">Volver a Tareas del Curso</a>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
