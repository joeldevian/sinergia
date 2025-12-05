<?php
require_once 'layout/header.php';
require_once '../../config/database.php';

// Ensure user is admin and summary data exists
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin' || !isset($_SESSION['pago_summary'])) {
    // Si no hay resumen o no es admin, redirigir a la gestión de pagos.
    header("Location: gestionar_pagos.php");
    exit();
}

// Retrieve summary data from session
$summary = $_SESSION['pago_summary'];
$pago_id = $summary['pago_id'];

// Fetch full payment and student details from DB (needed for PDF URL and potentially for display)
$query = "SELECT 
            p.numero_recibo, 
            e.nombres, 
            e.apellido_paterno, 
            e.codigo_estudiante 
          FROM pagos p
          JOIN estudiantes e ON p.id_estudiante = e.id
          WHERE p.id = ?";
$pago_details = select_one($query, "i", [$pago_id]);

if (!$pago_details) {
    $_SESSION['mensaje'] = "Error: No se pudieron cargar los detalles del pago para la confirmación.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: gestionar_pagos.php");
    exit();
}

// Clear the summary from session after use
unset($_SESSION['pago_summary']);
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: var(--primary-red);">
            <h4 class="mb-0">Pago Registrado</h4>
        </div>
        <div class="card-body text-center">
            <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
            <h5 class="card-title">¡Pago Registrado Exitosamente!</h5>
            <p class="card-text">El comprobante de pago N° <strong><?php echo htmlspecialchars($pago_details['numero_recibo']); ?></strong> para el estudiante <strong><?php echo htmlspecialchars($pago_details['nombres'] . ' ' . $pago_details['apellido_paterno']); ?></strong> se está abriendo en una nueva pestaña.</p>
            <p class="card-text">Si no se abre automáticamente, haz clic en el botón de abajo:</p>

            <div class="mt-4">
                <a id="btn-generar-pdf" href="../../controladores/generar_comprobante_pago_pdf.php?id=<?php echo $pago_id; ?>" class="btn btn-info" target="_blank"><i class="fas fa-file-pdf me-2"></i>Abrir Comprobante PDF</a>
                <a href="agregar_pago.php" class="btn btn-primary">Registrar Otro Pago</a>
                <a href="gestionar_pagos.php" class="btn btn-secondary">Ir a Gestión de Pagos</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfUrl = document.getElementById('btn-generar-pdf').href;
    if (pdfUrl) {
        window.open(pdfUrl, '_blank');
    }
});
</script>