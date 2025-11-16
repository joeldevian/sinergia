<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/database.php';

class RecursoController {

    // Método para obtener recursos por ID de asignación
    public static function obtenerRecursosPorAsignacion($conexion, $id_asignacion) {
        $stmt = $conexion->prepare("SELECT * FROM recursos_curso WHERE id_asignacion = ? ORDER BY fecha_creacion DESC");
        $stmt->bind_param("i", $id_asignacion);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $recursos = [];
        while ($row = $resultado->fetch_assoc()) {
            $recursos[] = $row;
        }
        $stmt->close();
        return $recursos;
    }

    // Método para obtener comunicaciones por ID de asignación
    public static function obtenerComunicacionesPorAsignacion($conexion, $id_asignacion) {
        $stmt = $conexion->prepare("SELECT * FROM comunicaciones_curso WHERE id_asignacion = ? ORDER BY fecha_creacion DESC");
        $stmt->bind_param("i", $id_asignacion);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $comunicaciones = [];
        while ($row = $resultado->fetch_assoc()) {
            $comunicaciones[] = $row;
        }
        $stmt->close();
        return $comunicaciones;
    }

    // Método para crear un nuevo recurso
    public static function crearRecurso($conexion, $id_asignacion, $titulo, $descripcion, $tipo_recurso, $file_data, $url_data) {
        $ruta = '';
        if ($tipo_recurso == 'archivo') {
            // Handle file upload
            $target_dir = "../uploads/recursos_curso/";
            $file_name = basename($file_data["name"]);
            $target_file = $target_dir . uniqid() . "_" . $file_name; // Unique filename to prevent overwrites
            $uploadOk = 1;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file already exists (though uniqid should prevent this)
            // if (file_exists($target_file)) {
            //     echo "Sorry, file already exists.";
            //     $uploadOk = 0;
            // }

            // Check file size (e.g., 5MB limit)
            if ($file_data["size"] > 5000000) {
                $_SESSION['error_message'] = "El archivo es demasiado grande.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
            if (!in_array($fileType, $allowed_types)) {
                $_SESSION['error_message'] = "Solo se permiten archivos JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                return false;
            } else {
                if (move_uploaded_file($file_data["tmp_name"], $target_file)) {
                    $ruta = basename($target_file);
                } else {
                    $_SESSION['error_message'] = "Hubo un error al subir tu archivo.";
                    return false;
                }
            }
        } elseif ($tipo_recurso == 'enlace') {
            $ruta = $url_data;
        }

        $stmt = $conexion->prepare("INSERT INTO recursos_curso (id_asignacion, titulo, descripcion, tipo_recurso, ruta) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_asignacion, $titulo, $descripcion, $tipo_recurso, $ruta);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Método para eliminar un recurso
    public static function eliminarRecurso($conexion, $id_recurso) {
        // First, get the resource details to delete the file if it's an 'archivo'
        $stmt_select = $conexion->prepare("SELECT tipo_recurso, ruta FROM recursos_curso WHERE id = ?");
        $stmt_select->bind_param("i", $id_recurso);
        $stmt_select->execute();
        $resultado_select = $stmt_select->get_result();
        $recurso_data = $resultado_select->fetch_assoc();
        $stmt_select->close();

        if ($recurso_data && $recurso_data['tipo_recurso'] == 'archivo') {
            $file_path = "../uploads/recursos_curso/" . $recurso_data['ruta'];
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the actual file
            }
        }

        $stmt = $conexion->prepare("DELETE FROM recursos_curso WHERE id = ?");
        $stmt->bind_param("i", $id_recurso);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Método para crear una nueva comunicación
    public static function crearComunicacion($conexion, $id_asignacion, $titulo, $mensaje) {
        $stmt = $conexion->prepare("INSERT INTO comunicaciones_curso (id_asignacion, titulo, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_asignacion, $titulo, $mensaje);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Método para eliminar una comunicación
    public static function eliminarComunicacion($conexion, $id_comunicacion) {
        $stmt = $conexion->prepare("DELETE FROM comunicaciones_curso WHERE id = ?");
        $stmt->bind_param("i", $id_comunicacion);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_asignacion = $_POST['id_asignacion'] ?? 0;

    if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
        $_SESSION['error_message'] = "Acceso denegado.";
        header("Location: ../../index.php");
        exit();
    }

    // Basic validation for id_asignacion
    if ($id_asignacion == 0) {
        $_SESSION['error_message'] = "ID de asignación no válido.";
        header("Location: ../vistas/docente/gestionar_recursos.php");
        exit();
    }

    switch ($action) {
        case 'crear_recurso':
            $titulo = $_POST['titulo_recurso'] ?? '';
            $descripcion = $_POST['descripcion_recurso'] ?? '';
            $tipo_recurso = $_POST['tipo_recurso'] ?? '';
            $file_data = $_FILES['archivo_recurso'] ?? null;
            $url_data = $_POST['url_recurso'] ?? '';

            if (RecursoController::crearRecurso($conexion, $id_asignacion, $titulo, $descripcion, $tipo_recurso, $file_data, $url_data)) {
                $_SESSION['success_message'] = "Recurso creado exitosamente.";
            } else {
                if (!isset($_SESSION['error_message'])) { // If error message not set by file upload logic
                    $_SESSION['error_message'] = "Error al crear el recurso.";
                }
            }
            break;

        case 'eliminar_recurso':
            $id_recurso = $_POST['id_recurso'] ?? 0;
            if (RecursoController::eliminarRecurso($conexion, $id_recurso)) {
                $_SESSION['success_message'] = "Recurso eliminado exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al eliminar el recurso.";
            }
            break;

        case 'crear_comunicacion':
            $titulo = $_POST['titulo_comunicacion'] ?? '';
            $mensaje = $_POST['mensaje_comunicacion'] ?? '';
            if (RecursoController::crearComunicacion($conexion, $id_asignacion, $titulo, $mensaje)) {
                $_SESSION['success_message'] = "Comunicado enviado exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al enviar el comunicado.";
            }
            break;

        case 'eliminar_comunicacion':
            $id_comunicacion = $_POST['id_comunicacion'] ?? 0;
            if (RecursoController::eliminarComunicacion($conexion, $id_comunicacion)) {
                $_SESSION['success_message'] = "Comunicado eliminado exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al eliminar el comunicado.";
            }
            break;

        default:
            $_SESSION['error_message'] = "Acción no válida.";
            break;
    }

    // Redirect back to the resource management page for the specific assignment
    header("Location: ../vistas/docente/gestionar_recursos_curso.php?id_asignacion=" . $id_asignacion);
    exit();
}

// Close connection if it was opened here (it's usually opened in conexion.php and closed in footer.php)
// if (isset($conexion) && $conexion instanceof mysqli) {
//     $conexion->close();
// }
?>
