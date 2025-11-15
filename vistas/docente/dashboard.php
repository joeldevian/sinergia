<?php require_once 'layout/header.php'; ?>

<h1 class="mb-4">Dashboard Docente</h1>
<p>Bienvenido al panel de docente, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
<p>Desde aquí podrás gestionar tus cursos, notas y asistencia.</p>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-mis-cursos mb-3">
            <div class="card-header">Mis Cursos</div>
            <div class="card-body">
                <h5 class="card-title">Ver Cursos Asignados</h5>
                <p class="card-text">Consulta los cursos que tienes a cargo.</p>
                <a href="mis_cursos.php" class="btn btn-light">Ir a Mis Cursos</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card card-gestionar-notas mb-3">
            <div class="card-header">Gestionar Notas</div>
            <div class="card-body">
                <h5 class="card-title">Registrar y Editar Notas</h5>
                <p class="card-text">Ingresa y modifica las calificaciones de tus estudiantes.</p>
                <a href="gestionar_notas.php" class="btn btn-light">Ir a Gestionar Notas</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card card-gestionar-asistencia mb-3">
            <div class="card-header">Gestionar Asistencia</div>
            <div class="card-body">
                <h5 class="card-title">Registrar Asistencia</h5>
                <p class="card-text">Lleva el control de la asistencia de tus estudiantes.</p>
                <a href="gestionar_asistencia.php" class="btn btn-light">Ir a Gestionar Asistencia</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
