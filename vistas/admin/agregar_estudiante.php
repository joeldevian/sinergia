<?php
require_once 'layout/header.php';
?>

<h1 class="mb-4">Agregar Nuevo Estudiante</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Datos del Estudiante</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/estudiante_controller.php" method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" id="dni" name="dni" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="codigo_estudiante" class="form-label">Código de Estudiante</label>
                    <input type="text" class="form-control" id="codigo_estudiante" name="codigo_estudiante" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select class="form-select" id="sexo" name="sexo">
                        <option value="O">Otro</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion">
                </div>
            </div>
            <hr>
            <h5 class="mt-4 mb-3">Datos de Usuario</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="form-text">Este será el nombre de usuario para que el estudiante inicie sesión.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">La contraseña para el primer inicio de sesión.</div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Guardar Estudiante</button>
                <a href="gestionar_estudiantes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>
