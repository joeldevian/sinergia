<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';

$id_curso = $_GET['id_curso'] ?? 0;
$fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d');

if ($id_curso == 0) {
    $_SESSION['mensaje'] = "ID de curso no válido.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: mis_cursos.php");
    exit();
}

// Fetch course details
$stmt_curso = $conexion->prepare("SELECT nombre_curso FROM cursos WHERE id = ?");
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$resultado_curso = $stmt_curso->get_result();
$curso = $resultado_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    $_SESSION['mensaje'] = "Curso no encontrado.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: mis_cursos.php");
    exit();
}

// Fetch students enrolled in this course
$query_estudiantes = "SELECT e.id, CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo
                      FROM matriculas m
                      JOIN estudiantes e ON m.id_estudiante = e.id
                      WHERE m.id_curso = ? AND m.estado = 'matriculado'
                      ORDER BY nombre_completo ASC";
$stmt_estudiantes = $conexion->prepare($query_estudiantes);
$stmt_estudiantes->bind_param("i", $id_curso);
$stmt_estudiantes->execute();
$resultado_estudiantes = $stmt_estudiantes->get_result();
$estudiantes = [];
while ($estudiante = $resultado_estudiantes->fetch_assoc()) {
    $estudiantes[] = $estudiante;
}
$stmt_estudiantes->close();

// Fetch existing attendance for the selected date and course
$asistencia_existente = [];
if (!empty($estudiantes)) {
    $estudiante_ids = array_column($estudiantes, 'id');
    $placeholders_estudiantes = implode(',', array_fill(0, count($estudiante_ids), '?'));
    $types = str_repeat('i', count($estudiante_ids)) . 'is';
    $params = array_merge($estudiante_ids, [$id_curso, $fecha_seleccionada]);

    $query_asistencia = "SELECT id_estudiante, estado FROM asistencia 
                         WHERE id_estudiante IN ($placeholders_estudiantes) 
                         AND id_curso = ? AND fecha = ?";
    $stmt_asistencia = $conexion->prepare($query_asistencia);
    $stmt_asistencia->bind_param($types, ...$params);
    $stmt_asistencia->execute();
    $resultado_asistencia = $stmt_asistencia->get_result();
    while ($asistencia = $resultado_asistencia->fetch_assoc()) {
        $asistencia_existente[$asistencia['id_estudiante']] = $asistencia['estado'];
    }
    $stmt_asistencia->close();
}
?>

<h1 class="mb-4">Gestionar Asistencia para: <?php echo htmlspecialchars($curso['nombre_curso']); ?></h1>

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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Seleccionar Fecha</h5>
    </div>
    <div class="card-body">
        <form action="gestionar_asistencia_curso.php" method="GET" class="row g-3 align-items-center">
            <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
            <div class="col-auto">
                <label for="fecha" class="col-form-label">Fecha:</label>
            </div>
            <div class="col-auto">
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Ver Asistencia</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Registro de Asistencia - Fecha: <?php echo htmlspecialchars($fecha_seleccionada); ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($estudiantes)): ?>
            <p class="text-center">No hay estudiantes matriculados en este curso.</p>
        <?php else: ?>
            <form action="../../controladores/asistencia_controller.php" method="POST">
                <input type="hidden" name="accion" value="guardar_asistencia">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover datatable">
                        <thead class="table-dark">
                            <tr>
                                <th>Estudiante</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></td>
                                    <td>
                                        <select class="form-select" name="asistencia[<?php echo $estudiante['id']; ?>]" required>
                                            <option value="asistio" <?php echo ($asistencia_existente[$estudiante['id']] ?? '') == 'asistio' ? 'selected' : ''; ?>>Asistió</option>
                                            <option value="falto" <?php echo ($asistencia_existente[$estudiante['id']] ?? '') == 'falto' ? 'selected' : ''; ?>>Faltó</option>
                                            <option value="tardanza" <?php echo ($asistencia_existente[$estudiante['id']] ?? '') == 'tardanza' ? 'selected' : ''; ?>>Tardanza</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">Guardar Asistencia</button>
                    <a href="mis_cursos.php" class="btn btn-secondary">Volver a Mis Cursos</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
