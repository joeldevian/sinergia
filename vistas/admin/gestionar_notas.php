<?php
require_once 'layout/header.php';
require_once '../../config/database.php'; // Cambiado de conexion.php a database.php

// Fetch all active courses for the admin
$query_cursos = "SELECT 
                    c.id, c.codigo_curso, c.nombre_curso, ca.nombre_carrera
                 FROM cursos c
                 JOIN carreras ca ON c.id_carrera = ca.id
                 WHERE c.estado = 'activo'
                 ORDER BY c.nombre_curso ASC";
$cursos = select_all($query_cursos); // Usando select_all() de database.php
?>

<h1 class="mb-4">Gestionar Notas (Administrador)</h1>
<p>Selecciona un curso para registrar o modificar las calificaciones de los estudiantes.</p>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Cursos Activos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Curso</th>
                        <th>Carrera</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cursos)): ?>
                        <?php foreach($cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_carrera']); ?></td>
                                <td>
                                    <a href="../docente/gestionar_notas_curso.php?id_curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info" title="Gestionar Notas">
                                        <i class="fas fa-graduation-cap"></i> Gestionar Notas
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay cursos activos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// $conexion->close(); // Eliminado
require_once 'layout/footer.php';
?>
