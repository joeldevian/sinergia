<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch courses from the database
$query = "SELECT c.id, c.codigo_curso, c.nombre_curso, c.creditos, c.horas_semanales, ca.nombre_carrera, c.ciclo, c.tipo, c.estado 
          FROM cursos c
          JOIN carreras ca ON c.id_carrera = ca.id
          ORDER BY ca.nombre_carrera, c.nombre_curso ASC";
$resultado = $conexion->query($query);
?>

<h1 class="mb-4">Gestionar Cursos</h1>

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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Cursos</h5>
        <a href="agregar_curso.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Curso
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Curso</th>
                        <th>Créditos</th>
                        <th>Horas Semanales</th>
                        <th>Carrera</th>
                        <th>Ciclo</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($curso = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['creditos']); ?></td>
                                <td><?php echo htmlspecialchars($curso['horas_semanales']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_carrera']); ?></td>
                                <td><?php echo htmlspecialchars($curso['ciclo']); ?></td>
                                <td><?php echo htmlspecialchars($curso['tipo']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $curso['estado'] == 'activo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($curso['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../controladores/curso_controller.php?accion=eliminar&id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este curso? Esto también afectará a las matrículas y evaluaciones asociadas.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay cursos registrados.</td>
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
