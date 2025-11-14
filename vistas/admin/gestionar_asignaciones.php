<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch active teachers for the dropdown
$query_docentes = "SELECT id, CONCAT(nombres, ' ', apellido_paterno) AS nombre_completo FROM docentes WHERE estado = 'activo' ORDER BY nombre_completo ASC";
$resultado_docentes = $conexion->query($query_docentes);

// Fetch active courses for the dropdown
$query_cursos = "SELECT id, nombre_curso FROM cursos WHERE estado = 'activo' ORDER BY nombre_curso ASC";
$resultado_cursos = $conexion->query($query_cursos);

// Fetch existing assignments
$query_asignaciones = "SELECT 
                            dc.id,
                            CONCAT(d.nombres, ' ', d.apellido_paterno) AS nombre_docente,
                            c.nombre_curso,
                            dc.periodo_academico
                         FROM docente_curso dc
                         JOIN docentes d ON dc.id_docente = d.id
                         JOIN cursos c ON dc.id_curso = c.id
                         ORDER BY dc.periodo_academico DESC, nombre_docente, nombre_curso";
$resultado_asignaciones = $conexion->query($query_asignaciones);
?>

<h1 class="mb-4">Gestionar Asignaciones (Docente-Curso)</h1>

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

<!-- Form to add new assignment -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Nueva Asignación</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/asignacion_controller.php" method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="id_docente" class="form-label">Docente</label>
                    <select class="form-select" id="id_docente" name="id_docente" required>
                        <option value="">Seleccione un docente</option>
                        <?php while($docente = $resultado_docentes->fetch_assoc()): ?>
                            <option value="<?php echo $docente['id']; ?>"><?php echo htmlspecialchars($docente['nombre_completo']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="id_curso" class="form-label">Curso</label>
                    <select class="form-select" id="id_curso" name="id_curso" required>
                        <option value="">Seleccione un curso</option>
                        <?php while($curso = $resultado_cursos->fetch_assoc()): ?>
                            <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre_curso']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="periodo_academico" class="form-label">Periodo Académico</label>
                    <input type="text" class="form-control" id="periodo_academico" name="periodo_academico" placeholder="Ej: 2025-II" required>
                </div>
                <div class="col-md-1 mb-3 d-grid">
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table of existing assignments -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Asignaciones Actuales</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Docente</th>
                        <th>Curso</th>
                        <th>Periodo Académico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_asignaciones->num_rows > 0): ?>
                        <?php while($asignacion = $resultado_asignaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asignacion['nombre_docente']); ?></td>
                                <td><?php echo htmlspecialchars($asignacion['nombre_curso']); ?></td>
                                <td><?php echo htmlspecialchars($asignacion['periodo_academico']); ?></td>
                                <td>
                                    <a href="../../controladores/asignacion_controller.php?accion=eliminar&id=<?php echo $asignacion['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta asignación?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay asignaciones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
