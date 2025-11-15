<?php require_once 'layout/header.php'; ?>

<h1 class="mb-4">Dashboard Estudiante</h1>
<p>Bienvenido al panel de estudiante, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
<p>Desde aquí podrás consultar tus calificaciones, horario, asistencia y pagos.</p>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-calificaciones mb-3">
            <div class="card-header">Calificaciones</div>
            <div class="card-body">
                <h5 class="card-title">Mis Calificaciones</h5>
                <p class="card-text">Consulta tus notas por curso y evaluación.</p>
                <a href="mis_calificaciones.php" class="btn btn-light">Ver Calificaciones</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card card-horario mb-3">
            <div class="card-header">Horario</div>
            <div class="card-body">
                <h5 class="card-title">Mi Horario</h5>
                <p class="card-text">Consulta tu horario de clases.</p>
                <a href="mi_horario.php" class="btn btn-light">Ver Horario</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card card-asistencia mb-3">
            <div class="card-header">Asistencia</div>
            <div class="card-body">
                <h5 class="card-title">Mi Asistencia</h5>
                <p class="card-text">Revisa tu registro de asistencia por curso.</p>
                <a href="mi_asistencia.php" class="btn btn-light">Ver Asistencia</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card card-pagos mb-3">
            <div class="card-header">Pagos</div>
            <div class="card-body">
                <h5 class="card-title">Mis Pagos</h5>
                <p class="card-text">Consulta el estado de tus pensiones y pagos.</p>
                <a href="mis_pagos.php" class="btn btn-light">Ver Pagos</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
