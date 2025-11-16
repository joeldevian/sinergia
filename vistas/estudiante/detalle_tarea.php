<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/tarea_controller.php';

$id_tarea = $_GET['id_tarea'] ?? 0;
$id_estudiante = $_SESSION['id_estudiante'] ?? 0; // Asumiendo que ya está en sesión desde mis_tareas.php

if ($id_tarea == 0 || $id_estudiante == 0) {
    echo "<div class='alert alert-danger'>ID de tarea o de estudiante no proporcionado.</div>";
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

// Fetch student's submission for this task
$entrega_estudiante = TareaController::obtenerEntregaEstudianteTarea($conexion, $id_tarea, $id_estudiante);

// Determine submission status
$estado_entrega = $entrega_estudiante['estado'] ?? 'pendiente';
if ($estado_entrega == 'pendiente' && strtotime($tarea['fecha_entrega']) < time()) {
    $estado_entrega = 'retrasado'; // Si no ha entregado y la fecha ya pasó
}

?>

<h1 class="mb-4">Detalle de Tarea: <?php echo htmlspecialchars($tarea['titulo']); ?></h1>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?php echo htmlspecialchars($tarea['titulo']); ?></h4>
    </div>
    <div class="card-body">
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($asignacion_data['nombre_curso'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($asignacion_data['periodo_academico'] ?? 'N/A'); ?>)</p>
        <p><strong>Fecha de Entrega:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['fecha_entrega']))); ?></p>
        <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?></p>
        <hr>
        <h5>Estado de tu Entrega:
            <?php
                $badge_class = '';
                switch ($estado_entrega) {
                    case 'pendiente': $badge_class = 'bg-secondary'; break;
                    case 'entregado': $badge_class = 'bg-primary'; break;
                    case 'calificado': $badge_class = 'bg-success'; break;
                    case 'retrasado': $badge_class = 'bg-danger'; break;
                    default: $badge_class = 'bg-secondary'; break;
                }
            ?>
            <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($estado_entrega)); ?></span>
        </h5>

        <?php if ($entrega_estudiante && $entrega_estudiante['calificacion'] !== NULL): ?>
            <p><strong>Calificación:</strong> <?php echo htmlspecialchars($entrega_estudiante['calificacion']); ?></p>
            <p><strong>Comentarios del Docente:</strong> <?php echo nl2br(htmlspecialchars($entrega_estudiante['comentarios_docente'])); ?></p>
        <?php endif; ?>

        <hr>

        <?php if (strtotime($tarea['fecha_entrega']) > time() || ($entrega_estudiante && $entrega_estudiante['estado'] != 'calificado')): // Permite entregar/re-entregar si no está calificado o no ha pasado la fecha ?>
            <h5><?php echo ($entrega_estudiante ? 'Actualizar' : 'Realizar'); ?> Entrega</h5>
            <form action="../../controladores/tarea_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="entregar_tarea">
                <input type="hidden" name="id_tarea" value="<?php echo $id_tarea; ?>">
                <input type="hidden" name="tipo_entrega" value="<?php echo htmlspecialchars($tarea['tipo_entrega']); ?>">

                <?php if ($tarea['tipo_entrega'] == 'archivo'): ?>
                    <div class="mb-3">
                        <label for="archivo_entrega" class="form-label">Subir Archivo</label>
                        <input type="file" class="form-control" id="archivo_entrega" name="archivo_entrega" <?php echo ($entrega_estudiante && $entrega_estudiante['ruta_archivo']) ? '' : 'required'; ?>>
                        <?php if ($entrega_estudiante && $entrega_estudiante['ruta_archivo']): ?>
                            <small class="form-text text-muted">Archivo actual: <a href="../../uploads/entregas_tarea/<?php echo htmlspecialchars($entrega_estudiante['ruta_archivo']); ?>" target="_blank"><?php echo htmlspecialchars($entrega_estudiante['ruta_archivo']); ?></a></small>
                        <?php endif; ?>
                    </div>
                <?php else: // tipo_entrega == 'texto' ?>
                    <div class="mb-3">
                        <label for="texto_entrega" class="form-label">Escribe tu Entrega</label>
                        <textarea class="form-control" id="texto_entrega" name="texto_entrega" rows="8" required><?php echo htmlspecialchars($entrega_estudiante['texto_entrega'] ?? ''); ?></textarea>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-success"><?php echo ($entrega_estudiante ? 'Actualizar' : 'Realizar'); ?> Entrega</button>
            </form>
        <?php elseif ($estado_entrega == 'calificado'): ?>
            <div class="alert alert-info">Esta tarea ya ha sido calificada. No se permiten más entregas.</div>
        <?php else: ?>
            <div class="alert alert-warning">La fecha de entrega ha pasado y no has realizado una entrega.</div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <a href="mis_tareas.php" class="btn btn-secondary">Volver a Mis Tareas</a>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
