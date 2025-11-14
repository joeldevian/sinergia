<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - INSTITUTO SINERGIA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilos.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white">INSTITUTO SINERGIA</div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a href="gestionar_estudiantes.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-user-graduate me-2"></i>Estudiantes
            </a>
            <a href="gestionar_docentes.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-chalkboard-teacher me-2"></i>Docentes
            </a>
            <a href="gestionar_cursos.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-book me-2"></i>Cursos
            </a>
            <a href="gestionar_evaluaciones.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-clipboard-check me-2"></i>Configuración de Evaluaciones
            </a>
            <a href="gestionar_notas.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-graduation-cap me-2"></i>Gestionar Notas
            </a>
            <a href="gestionar_matriculas.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-clipboard-list me-2"></i>Matrículas
            </a>
            <a href="gestionar_asignaciones.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-link me-2"></i>Asignaciones
            </a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Mi Perfil</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../../controladores/logout_controller.php">Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
