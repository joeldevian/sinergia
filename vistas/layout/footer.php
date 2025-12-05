<?php
// This file will be included by role-specific footers.
// It expects $includeDataTablesJs to be set by the including file if DataTables JS is needed.
?>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($includeDataTablesJs) && $includeDataTablesJs): ?>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando del _START_ al _END_ de _TOTAL_ registros",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });
</script>
<?php endif; ?>
<!-- Menu Toggle Script -->
<script>
    document.getElementById("menu-toggle").addEventListener("click", function(e) {
        e.preventDefault();
        document.getElementById("wrapper").classList.toggle("toggled");
    });
</script>

<!-- Bootstrap 5 Form Validation Script -->
<script>
(() => {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>

<!-- Toastify JS -->
<script src="../../assets/js/toastify.min.js"></script>
<script src="../../assets/js/notificaciones.js"></script>

<!-- Script para mostrar notificaciones desde la sesión de PHP -->
<?php
if (isset($_SESSION['mensaje'])) {
    $message = $_SESSION['mensaje'];
    $type = $_SESSION['mensaje_tipo'] ?? 'info'; // Default to 'info' if type is not set
    // Limpiar para que no se muestre de nuevo
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Asumimos que la función mostrarNotificacion ya existe en notificaciones.js
            // y que puede manejar tipos como 'success', 'danger', 'info', 'warning'
            mostrarNotificacion('" . addslashes($message) . "', '" . addslashes($type) . "');
        });
    </script>";
}
?>

</body>
</html>
