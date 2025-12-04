<?php
require_once 'layout/header.php';
require_once '../../config/database.php';

// Validar que el usuario sea estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../../index.php");
    exit();
}
?>

<h1 class="mb-4">Mis Cursos</h1>
<p>Aquí puedes ver los cursos en los que estás matriculado actualmente. Haz clic en cualquiera de ellos para ver los detalles.</p>

<!-- Fila de Mis Cursos -->
<div class="row mt-4">
    <?php
    // Obtener ID del estudiante
    $id_estudiante_actual = 0;
    if (isset($_SESSION['user_id'])) {
        $estudiante_info = select_one("SELECT id FROM estudiantes WHERE id_user = ? LIMIT 1", "i", [$_SESSION['user_id']]);
        if ($estudiante_info) {
            $id_estudiante_actual = $estudiante_info['id'];
        }
    }
    
    $mis_cursos = [];
    if ($id_estudiante_actual > 0) {
        $query_mis_cursos = "SELECT
                                c.id AS id_curso,
                                c.codigo_curso,
                                c.nombre_curso,
                                c.creditos,
                                dc.id AS id_asignacion,
                                d.nombres AS docente_nombres,
                                d.apellido_paterno AS docente_apellido
                            FROM matriculas m
                            JOIN cursos c ON m.id_curso = c.id
                            LEFT JOIN docente_curso dc ON c.id = dc.id_curso AND m.periodo_academico = dc.periodo_academico
                            LEFT JOIN docentes d ON dc.id_docente = d.id
                            WHERE m.id_estudiante = ? AND m.estado = 'activo'
                            GROUP BY m.id_curso, m.periodo_academico
                            ORDER BY c.nombre_curso ASC";
        $mis_cursos = select_all($query_mis_cursos, "i", [$id_estudiante_actual]);
    }

    if (!empty($mis_cursos)):
        foreach ($mis_cursos as $curso):
            $nombre_docente_completo = ($curso['docente_nombres'] ? htmlspecialchars($curso['docente_nombres'] . ' ' . $curso['docente_apellido']) : 'No asignado');
            // Si no hay asignación, el enlace no se puede crear, por lo que la tarjeta no será clickeable.
            $enlace_detalle = $curso['id_asignacion'] ? "detalle_curso.php?id_asignacion=" . $curso['id_asignacion'] : "#";
            $clase_enlace = $curso['id_asignacion'] ? "text-decoration-none" : "text-decoration-none disabled-link";
            $titulo_enlace = $curso['id_asignacion'] ? 'Ver detalles del curso' : 'Detalles no disponibles (sin docente asignado)';
    ?>
    <div class="col-xl-4 col-md-6 mb-4">
        <a href="<?php echo $enlace_detalle; ?>" class="<?php echo $clase_enlace; ?>" title="<?php echo $titulo_enlace; ?>">
            <div class="card h-100 card-horario shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Código:</strong> <?php echo htmlspecialchars($curso['codigo_curso']); ?></p>
                    <p class="mb-1"><strong>Docente:</strong> <?php echo $nombre_docente_completo; ?></p>
                    <p class="mb-0"><strong>Créditos:</strong> <?php echo htmlspecialchars($curso['creditos']); ?></p>
                </div>
            </div>
        </a>
    </div>
    <?php
        endforeach;
    else:
    ?>
    <div class="col-12">
        <div class="alert alert-info">No estás matriculado en cursos activos actualmente.</div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
