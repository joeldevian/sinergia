<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/recurso_controller.php'; // We will create this controller

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

// Fetch existing resources
$recursos = RecursoController::obtenerRecursosPorAsignacion($conexion, $id_asignacion);

// Fetch existing communications
$comunicaciones = RecursoController::obtenerComunicacionesPorAsignacion($conexion, $id_asignacion);

?>

<h1 class="mb-4">Gestionar Recursos y Comunicaciones para: <?php echo htmlspecialchars($asignacion_data['nombre_curso']); ?> (<?php echo htmlspecialchars($asignacion_data['periodo_academico']); ?>)</h1>
<p>Docente: <?php echo htmlspecialchars($asignacion_data['docente_nombres'] . ' ' . $asignacion_data['docente_apellido_paterno'] . ' ' . $asignacion_data['docente_apellido_materno']); ?></p>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Subir Nuevo Recurso</h5>
            </div>
            <div class="card-body">
                <form action="../../controladores/recurso_controller.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="crear_recurso">
                    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                    <div class="mb-3">
                        <label for="titulo_recurso" class="form-label">Título del Recurso</label>
                        <input type="text" class="form-control" id="titulo_recurso" name="titulo_recurso" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_recurso" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_recurso" name="descripcion_recurso" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_recurso" class="form-label">Tipo de Recurso</label>
                        <select class="form-select" id="tipo_recurso" name="tipo_recurso" required>
                            <option value="">Seleccione...</option>
                            <option value="archivo">Archivo</option>
                            <option value="enlace">Enlace</option>
                        </select>
                    </div>
                    <div class="mb-3" id="div_archivo" style="display: none;">
                        <label for="archivo_recurso" class="form-label">Seleccionar Archivo</label>
                        <input type="file" class="form-control" id="archivo_recurso" name="archivo_recurso">
                    </div>
                    <div class="mb-3" id="div_enlace" style="display: none;">
                        <label for="url_recurso" class="form-label">URL del Enlace</label>
                        <input type="url" class="form-control" id="url_recurso" name="url_recurso">
                    </div>
                    <button type="submit" class="btn btn-primary">Subir Recurso</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Enviar Nuevo Comunicado</h5>
            </div>
            <div class="card-body">
                <form action="../../controladores/recurso_controller.php" method="POST">
                    <input type="hidden" name="action" value="crear_comunicacion">
                    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                    <div class="mb-3">
                        <label for="titulo_comunicacion" class="form-label">Título del Comunicado</label>
                        <input type="text" class="form-control" id="titulo_comunicacion" name="titulo_comunicacion" required>
                    </div>
                    <div class="mb-3">
                        <label for="mensaje_comunicacion" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="mensaje_comunicacion" name="mensaje_comunicacion" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-info">Enviar Comunicado</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Recursos del Curso</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recursos)): ?>
                    <ul class="list-group">
                        <?php foreach ($recursos as $recurso): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($recurso['titulo']); ?></strong>
                                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($recurso['descripcion']); ?></p>
                                    <?php if ($recurso['tipo_recurso'] == 'archivo'): ?>
                                        <a href="../../uploads/recursos_curso/<?php echo htmlspecialchars($recurso['ruta']); ?>" target="_blank" class="badge bg-secondary"><i class="fas fa-download"></i> Descargar Archivo</a>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($recurso['ruta']); ?>" target="_blank" class="badge bg-secondary"><i class="fas fa-external-link-alt"></i> Ir al Enlace</a>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">Publicado: <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_creacion'])); ?></small>
                                </div>
                                <form action="../../controladores/recurso_controller.php" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este recurso?');">
                                    <input type="hidden" name="action" value="eliminar_recurso">
                                    <input type="hidden" name="id_recurso" value="<?php echo $recurso['id']; ?>">
                                    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No hay recursos disponibles para este curso.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Comunicados del Curso</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comunicaciones)): ?>
                    <ul class="list-group">
                        <?php foreach ($comunicaciones as $comunicacion): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($comunicacion['titulo']); ?></strong>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comunicacion['mensaje'])); ?></p>
                                    <small class="text-muted d-block">Publicado: <?php echo date('d/m/Y H:i', strtotime($comunicacion['fecha_creacion'])); ?></small>
                                </div>
                                <form action="../../controladores/recurso_controller.php" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este comunicado?');">
                                    <input type="hidden" name="action" value="eliminar_comunicacion">
                                    <input type="hidden" name="id_comunicacion" value="<?php echo $comunicacion['id']; ?>">
                                    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No hay comunicados para este curso.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoRecurso = document.getElementById('tipo_recurso');
    const divArchivo = document.getElementById('div_archivo');
    const divEnlace = document.getElementById('div_enlace');
    const archivoRecurso = document.getElementById('archivo_recurso');
    const urlRecurso = document.getElementById('url_recurso');

    function toggleRecursoFields() {
        if (tipoRecurso.value === 'archivo') {
            divArchivo.style.display = 'block';
            archivoRecurso.setAttribute('required', 'required');
            divEnlace.style.display = 'none';
            urlRecurso.removeAttribute('required');
            urlRecurso.value = ''; // Clear URL if switching to file
        } else if (tipoRecurso.value === 'enlace') {
            divEnlace.style.display = 'block';
            urlRecurso.setAttribute('required', 'required');
            divArchivo.style.display = 'none';
            archivoRecurso.removeAttribute('required');
            archivoRecurso.value = ''; // Clear file if switching to link
        } else {
            divArchivo.style.display = 'none';
            divEnlace.style.display = 'none';
            archivoRecurso.removeAttribute('required');
            urlRecurso.removeAttribute('required');
            archivoRecurso.value = '';
            urlRecurso.value = '';
        }
    }

    tipoRecurso.addEventListener('change', toggleRecursoFields);

    // Initial call to set correct state based on default value (if any)
    toggleRecursoFields();
});
</script>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
