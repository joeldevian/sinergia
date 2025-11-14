<?php require_once 'layout/header.php'; ?>

<h1 class="mb-4">Dashboard</h1>
<p>Bienvenido al panel de administración, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
<p>Desde aquí podrás gestionar los diferentes módulos del sistema.</p>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Estudiantes</div>
            <div class="card-body">
                <h5 class="card-title">Gestionar Estudiantes</h5>
                <p class="card-text">Ver, agregar, editar y eliminar estudiantes.</p>
                <a href="gestionar_estudiantes.php" class="btn btn-light">Ir a Estudiantes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Docentes</div>
            <div class="card-body">
                <h5 class="card-title">Gestionar Docentes</h5>
                <p class="card-text">Ver, agregar, editar y eliminar docentes.</p>
                <a href="gestionar_docentes.php" class="btn btn-light">Ir a Docentes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Cursos</div>
            <div class="card-body">
                <h5 class="card-title">Gestionar Cursos</h5>
                <p class="card-text">Ver, agregar, editar y eliminar cursos.</p>
                <a href="gestionar_cursos.php" class="btn btn-light">Ir a Cursos</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
