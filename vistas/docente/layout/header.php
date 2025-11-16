<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../../index.php");
    exit();
}

$pageTitle = "Panel de Docente";
// $includeDataTablesCss = false; // DataTables CSS not needed for docente
require_once '../../vistas/layout/header.php';
require_once '../../vistas/layout/sidebar.php';
?>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="mi_perfil.php">Mi Perfil</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../../controladores/logout_controller.php">Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
