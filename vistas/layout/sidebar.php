<?php
// This file is included by the common header.
// It expects $_SESSION['rol'] to be set.
// It also expects $current_page to be set by the role-specific header for active link highlighting.

$current_page = basename($_SERVER['PHP_SELF']); // Get the current page filename

function isActive($pageName, $currentPage) {
    return ($pageName === $currentPage) ? 'active' : '';
}

if (isset($_SESSION['rol'])) {
    echo "<!-- ROL: " . $_SESSION['rol'] . " -->";
    switch ($_SESSION['rol']) {
        case 'admin':
            ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo isActive('dashboard.php', $current_page); ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="gestionar_estudiantes.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_estudiantes.php', $current_page); ?>">
                <i class="fas fa-user-graduate me-2"></i>Estudiantes
            </a>
            <a href="gestionar_docentes.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_docentes.php', $current_page); ?>">
                <i class="fas fa-chalkboard-teacher me-2"></i>Docentes
            </a>
            <a href="gestionar_cursos.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_cursos.php', $current_page); ?>">
                <i class="fas fa-book me-2"></i>Cursos
            </a>
            <a href="gestionar_evaluaciones.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_evaluaciones.php', $current_page); ?>">
                <i class="fas fa-clipboard-check me-2"></i>Configuración de Evaluaciones
            </a>
            <a href="gestionar_notas.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_notas.php', $current_page); ?>">
                <i class="fas fa-graduation-cap me-2"></i>Gestionar Notas
            </a>
            <a href="gestionar_matriculas.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_matriculas.php', $current_page); ?>">
                <i class="fas fa-clipboard-list me-2"></i>Matrículas
            </a>
            <a href="gestionar_asignaciones.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_asignaciones.php', $current_page); ?>">
                <i class="fas fa-link me-2"></i>Asignaciones
            </a>
            <a href="gestionar_pagos.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_pagos.php', $current_page); ?>">
                <i class="fas fa-money-bill-wave me-2"></i>Gestión de Pagos
            </a>
            <?php
            break;
        case 'docente':
            ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo isActive('dashboard.php', $current_page); ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="mis_cursos.php" class="list-group-item list-group-item-action <?php echo isActive('mis_cursos.php', $current_page); ?>">
                <i class="fas fa-book me-2"></i>Mis Cursos
            </a>
            <a href="gestionar_recursos.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_recursos.php', $current_page); ?>">
                <i class="fas fa-folder-open me-2"></i>Recursos del Curso
            </a>
            <a href="gestionar_notas.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_notas.php', $current_page); ?>">
                <i class="fas fa-graduation-cap me-2"></i>Gestionar Notas
            </a>
            <a href="gestionar_asistencia.php" class="list-group-item list-group-item-action <?php echo isActive('gestionar_asistencia.php', $current_page); ?>">
                <i class="fas fa-clipboard-check me-2"></i>Gestionar Asistencia
            </a>
            <?php
            break;
        case 'estudiante':
            ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo isActive('dashboard.php', $current_page); ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="mis_calificaciones.php" class="list-group-item list-group-item-action <?php echo isActive('mis_calificaciones.php', $current_page); ?>">
                <i class="fas fa-graduation-cap me-2"></i>Mis Calificaciones
            </a>
            <a href="mi_horario.php" class="list-group-item list-group-item-action <?php echo isActive('mi_horario.php', $current_page); ?>">
                <i class="fas fa-calendar-alt me-2"></i>Mi Horario
            </a>
            <a href="mi_asistencia.php" class="list-group-item list-group-item-action <?php echo isActive('mi_asistencia.php', $current_page); ?>">
                <i class="fas fa-clipboard-check me-2"></i>Mi Asistencia
            </a>
            <a href="mis_pagos.php" class="list-group-item list-group-item-action <?php echo isActive('mis_pagos.php', $current_page); ?>">
                <i class="fas fa-money-bill-wave me-2"></i>Mis Pagos
            </a>
            <?php
            break;
    }
}
?>