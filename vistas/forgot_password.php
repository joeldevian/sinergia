<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - INSTITUTO SINERGIA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-danger text-white">
                        <h3 class="mb-0">Recuperar Contraseña</h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-center">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                        
                        <?php 
                        session_start();
                        if (isset($_SESSION['status_message'])): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['status_type']); ?> text-center" role="alert">
                                <?php 
                                echo $_SESSION['status_message']; 
                                unset($_SESSION['status_message']);
                                unset($_SESSION['status_type']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="../controladores/forgot_password_controller.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">Enviar Enlace de Recuperación</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="../index.php">Volver al Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>