<?php
require_once 'layout/header.php';
require_once '../../config/database.php';

// Obtener el ID del estudiante de la URL para pre-seleccionarlo
$id_estudiante_preseleccionado = filter_input(INPUT_GET, 'id_estudiante', FILTER_VALIDATE_INT);

// Fetch active students for the dropdown
$query_estudiantes = "SELECT id, CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno, ' (', codigo_estudiante, ')') AS nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY apellido_paterno ASC";
$estudiantes = select_all($query_estudiantes);

// Define payment concepts
$conceptos_pago = [
    'Matrícula',
    'Pensión Mensual',
    'Derecho de Examen',
    'Certificado de Estudios',
    'Carnet de Estudiante',
    'Otro'
];

// Define payment methods
$metodos_pago = [
    'Efectivo',
    'Transferencia Bancaria',
    'Tarjeta de Crédito/Débito',
    'Yape/Plin'
];
?>

<h1 class="mb-4">Registrar Nuevo Pago</h1>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Información del Pago</h5>
    </div>
    <div class="card-body">
        <form action="../../controladores/pago_controller.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="accion" value="agregar_pago_general">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_estudiante" class="form-label">Estudiante</label>
                    <select class="form-select" id="id_estudiante" name="id_estudiante" required>
                        <option value="">Seleccione un estudiante</option>
                        <?php foreach($estudiantes as $estudiante): ?>
                            <option value="<?php echo $estudiante['id']; ?>" <?php echo ($id_estudiante_preseleccionado == $estudiante['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($estudiante['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor, seleccione un estudiante.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese una fecha de pago.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="concepto" class="form-label">Concepto de Pago</label>
                    <input class="form-control" list="conceptos-list" id="concepto" name="concepto" required placeholder="Escriba o seleccione un concepto...">
                    <datalist id="conceptos-list">
                        <?php foreach ($conceptos_pago as $concepto): ?>
                            <option value="<?php echo htmlspecialchars($concepto); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <div class="invalid-feedback">
                        Por favor, ingrese un concepto de pago.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="monto" class="form-label">Monto (S/)</label>
                    <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0" required>
                    <div class="invalid-feedback">
                        Por favor, ingrese un monto válido.
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                        <option value="">Seleccione un método</option>
                        <?php foreach ($metodos_pago as $metodo): ?>
                            <option value="<?php echo htmlspecialchars($metodo); ?>"><?php echo htmlspecialchars($metodo); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Por favor, seleccione un método de pago.
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Guardar Pago y Generar Recibo</button>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
// Inicializar Select2 en el selector de estudiantes
$(document).ready(function() {
    $('#id_estudiante').select2({
        placeholder: 'Busque y seleccione un estudiante',
        width: '100%'
    });
});
</script>
