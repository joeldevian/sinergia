<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/database.php';

// Validar que el usuario sea docente
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../../index.php");
    exit();
}

// Validar que se recibió un ID de curso
if (!isset($_GET['id_curso']) || !is_numeric($_GET['id_curso'])) {
    echo "ID de curso no válido.";
    require_once 'layout/footer.php';
    exit();
}

$id_curso = $_GET['id_curso'];

// Obtener información del curso
$curso = select_one("SELECT nombre_curso FROM cursos WHERE id = ?", "i", [$id_curso]);

if (!$curso) {
    echo "Curso no encontrado.";
    require_once 'layout/footer.php';
    exit();
}

// Obtener la lista de estudiantes matriculados en el curso
$query_estudiantes = "SELECT 
                        e.codigo_estudiante,
                        e.nombres,
                        e.apellido_paterno,
                        e.apellido_materno,
                        e.email
                      FROM estudiantes e
                      JOIN matriculas m ON e.id = m.id_estudiante
                      WHERE m.id_curso = ?
                      ORDER BY e.apellido_paterno, e.apellido_materno, e.nombres";
$estudiantes = select_all($query_estudiantes, "i", [$id_curso]);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Lista de Estudiantes</h1>
    <a href="mis_cursos.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Mis Cursos
    </a>
</div>


<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Curso: <?php echo htmlspecialchars($curso['nombre_curso']); ?></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($estudiantes)): ?>
                        <?php foreach($estudiantes as $estudiante): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($estudiante['codigo_estudiante']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['nombres']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay estudiantes matriculados en este curso.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>
