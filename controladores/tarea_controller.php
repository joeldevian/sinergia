<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/database.php'; // Assuming database.php has helper functions like select_one, execute_cud

class TareaController {

    // --- Métodos para Tareas (Docente) ---

    // Obtener tareas por ID de asignación
    public static function obtenerTareasPorAsignacion($conexion, $id_asignacion) {
        $stmt = $conexion->prepare("SELECT * FROM tareas WHERE id_asignacion = ? ORDER BY fecha_entrega DESC");
        $stmt->bind_param("i", $id_asignacion);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $tareas = [];
        while ($row = $resultado->fetch_assoc()) {
            $tareas[] = $row;
        }
        $stmt->close();
        return $tareas;
    }

    // Obtener detalles de una tarea específica
    public static function obtenerTareaPorId($conexion, $id_tarea) {
        $stmt = $conexion->prepare("SELECT * FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id_tarea);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $tarea = $resultado->fetch_assoc();
        $stmt->close();
        return $tarea;
    }

    // Crear una nueva tarea
    public static function crearTarea($conexion, $id_asignacion, $titulo, $descripcion, $fecha_entrega, $tipo_entrega) {
        $stmt = $conexion->prepare("INSERT INTO tareas (id_asignacion, titulo, descripcion, fecha_entrega, tipo_entrega) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_asignacion, $titulo, $descripcion, $fecha_entrega, $tipo_entrega);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Editar una tarea existente
    public static function editarTarea($conexion, $id_tarea, $titulo, $descripcion, $fecha_entrega, $tipo_entrega, $estado) {
        $stmt = $conexion->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, fecha_entrega = ?, tipo_entrega = ?, estado = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $titulo, $descripcion, $fecha_entrega, $tipo_entrega, $estado, $id_tarea);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Eliminar una tarea
    public static function eliminarTarea($conexion, $id_tarea) {
        $stmt = $conexion->prepare("DELETE FROM tareas WHERE id = ?");
        $stmt->bind_param("i", $id_tarea);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // --- Métodos para Entregas (Docente y Estudiante) ---

    // Obtener entregas de una tarea específica (para docente)
    public static function obtenerEntregasPorTarea($conexion, $id_tarea) {
        $stmt = $conexion->prepare("SELECT et.*, e.nombres, e.apellido_paterno, e.apellido_materno FROM entregas_tarea et JOIN estudiantes e ON et.id_estudiante = e.id WHERE et.id_tarea = ? ORDER BY et.fecha_entrega DESC");
        $stmt->bind_param("i", $id_tarea);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $entregas = [];
        while ($row = $resultado->fetch_assoc()) {
            $entregas[] = $row;
        }
        $stmt->close();
        return $entregas;
    }

    // Obtener las entregas de un estudiante para todas sus tareas (para estudiante)
    public static function obtenerMisEntregas($conexion, $id_estudiante) {
        $stmt = $conexion->prepare("SELECT et.*, t.titulo AS tarea_titulo, t.fecha_entrega AS tarea_fecha_entrega, c.nombre_curso FROM entregas_tarea et JOIN tareas t ON et.id_tarea = t.id JOIN docente_curso dc ON t.id_asignacion = dc.id JOIN cursos c ON dc.id_curso = c.id WHERE et.id_estudiante = ? ORDER BY t.fecha_entrega DESC");
        $stmt->bind_param("i", $id_estudiante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $entregas = [];
        while ($row = $resultado->fetch_assoc()) {
            $entregas[] = $row;
        }
        $stmt->close();
        return $entregas;
    }

    // Obtener una entrega específica de un estudiante para una tarea
    public static function obtenerEntregaEstudianteTarea($conexion, $id_tarea, $id_estudiante) {
        $stmt = $conexion->prepare("SELECT * FROM entregas_tarea WHERE id_tarea = ? AND id_estudiante = ?");
        $stmt->bind_param("ii", $id_tarea, $id_estudiante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $entrega = $resultado->fetch_assoc();
        $stmt->close();
        return $entrega;
    }

    // Estudiante: Subir/Actualizar entrega
    public static function entregarTarea($conexion, $id_tarea, $id_estudiante, $tipo_entrega, $file_data = null, $texto_entrega = null) {
        $ruta_archivo = null;
        $estado_entrega = 'entregado';

        // Verificar si ya existe una entrega para esta tarea y estudiante
        $entrega_existente = self::obtenerEntregaEstudianteTarea($conexion, $id_tarea, $id_estudiante);

        // Obtener la fecha de entrega de la tarea para determinar si está retrasada
        $tarea = self::obtenerTareaPorId($conexion, $id_tarea);
        if ($tarea && strtotime($tarea['fecha_entrega']) < time()) {
            $estado_entrega = 'retrasado';
        }

        if ($tipo_entrega == 'archivo' && $file_data) {
            $target_dir = __DIR__ . "/../uploads/entregas_tarea/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = basename($file_data["name"]);
            $unique_filename = uniqid("entrega_") . "_" . $file_name;
            $target_file = $target_dir . $unique_filename;
            $uploadOk = 1;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validaciones de archivo
            if ($file_data["size"] > 10000000) { // 10MB limit
                $_SESSION['error_message'] = "El archivo es demasiado grande (máx. 10MB).";
                $uploadOk = 0;
            }
            $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'jpg', 'png', 'jpeg'];
            if (!in_array($fileType, $allowed_types)) {
                $_SESSION['error_message'] = "Tipo de archivo no permitido.";
                $uploadOk = 0;
            }

            if ($uploadOk == 0) {
                return false;
            } else {
                if (move_uploaded_file($file_data["tmp_name"], $target_file)) {
                    $ruta_archivo = $unique_filename;
                } else {
                    $_SESSION['error_message'] = "Hubo un error al subir el archivo.";
                    return false;
                }
            }
        }

        if ($entrega_existente) {
            // Actualizar entrega existente
            $sql = "UPDATE entregas_tarea SET fecha_entrega = CURRENT_TIMESTAMP, estado = ?, ";
            $params = [$estado_entrega];
            $types = "s";

            if ($tipo_entrega == 'archivo') {
                $sql .= "ruta_archivo = ?, texto_entrega = NULL ";
                $params[] = $ruta_archivo;
                $types .= "s";
                // Eliminar archivo anterior si existe
                if ($entrega_existente['ruta_archivo'] && file_exists($target_dir . $entrega_existente['ruta_archivo'])) {
                    unlink($target_dir . $entrega_existente['ruta_archivo']);
                }
            } else { // texto
                $sql .= "texto_entrega = ?, ruta_archivo = NULL ";
                $params[] = $texto_entrega;
                $types .= "s";
            }
            $sql .= "WHERE id_tarea = ? AND id_estudiante = ?";
            $params[] = $id_tarea;
            $params[] = $id_estudiante;
            $types .= "ii";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            $stmt->close();
            return $result;

        } else {
            // Insertar nueva entrega
            $sql = "INSERT INTO entregas_tarea (id_tarea, id_estudiante, ruta_archivo, texto_entrega, estado) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            if ($tipo_entrega == 'archivo') {
                $stmt->bind_param("iiss", $id_tarea, $id_estudiante, $ruta_archivo, $texto_entrega, $estado_entrega);
            } else { // texto
                $stmt->bind_param("iiss", $id_tarea, $id_estudiante, $ruta_archivo, $texto_entrega, $estado_entrega);
            }
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
    }

    // Docente: Calificar entrega
    public static function calificarEntrega($conexion, $id_entrega, $calificacion, $comentarios_docente) {
        $stmt = $conexion->prepare("UPDATE entregas_tarea SET calificacion = ?, comentarios_docente = ?, estado = 'calificado' WHERE id = ?");
        $stmt->bind_param("dsi", $calificacion, $comentarios_docente, $id_entrega);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

// --- Manejo de solicitudes POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_asignacion = $_POST['id_asignacion'] ?? 0; // Para acciones de docente
    $id_tarea = $_POST['id_tarea'] ?? 0; // Para acciones de tarea/entrega
    $id_estudiante = $_SESSION['id_estudiante'] ?? 0; // Asumiendo que el ID del estudiante está en sesión

    // Validar rol y acceso
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Acceso denegado. Inicie sesión.";
        header("Location: ../../index.php");
        exit();
    }

    switch ($action) {
        // Acciones de Docente
        case 'crear_tarea':
            if ($_SESSION['rol'] !== 'docente') { $_SESSION['error_message'] = "Acceso denegado."; break; }
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $fecha_entrega = $_POST['fecha_entrega'] ?? '';
            $tipo_entrega = $_POST['tipo_entrega'] ?? 'archivo';
            if (TareaController::crearTarea($conexion, $id_asignacion, $titulo, $descripcion, $fecha_entrega, $tipo_entrega)) {
                $_SESSION['success_message'] = "Tarea creada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al crear la tarea.";
            }
            header("Location: ../vistas/docente/gestionar_tareas_curso.php?id_asignacion=" . $id_asignacion);
            exit();

        case 'editar_tarea':
            if ($_SESSION['rol'] !== 'docente') { $_SESSION['error_message'] = "Acceso denegado."; break; }
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $fecha_entrega = $_POST['fecha_entrega'] ?? '';
            $tipo_entrega = $_POST['tipo_entrega'] ?? 'archivo';
            $estado = $_POST['estado'] ?? 'activa';
            if (TareaController::editarTarea($conexion, $id_tarea, $titulo, $descripcion, $fecha_entrega, $tipo_entrega, $estado)) {
                $_SESSION['success_message'] = "Tarea actualizada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al actualizar la tarea.";
            }
            header("Location: ../vistas/docente/gestionar_tareas_curso.php?id_asignacion=" . $id_asignacion);
            exit();

        case 'eliminar_tarea':
            if ($_SESSION['rol'] !== 'docente') { $_SESSION['error_message'] = "Acceso denegado."; break; }
            if (TareaController::eliminarTarea($conexion, $id_tarea)) {
                $_SESSION['success_message'] = "Tarea eliminada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al eliminar la tarea.";
            }
            header("Location: ../vistas/docente/gestionar_tareas_curso.php?id_asignacion=" . $id_asignacion);
            exit();

        case 'calificar_entrega':
            if ($_SESSION['rol'] !== 'docente') { $_SESSION['error_message'] = "Acceso denegado."; break; }
            $id_entrega = $_POST['id_entrega'] ?? 0;
            $calificacion = $_POST['calificacion'] ?? null;
            $comentarios_docente = $_POST['comentarios_docente'] ?? '';
            if (TareaController::calificarEntrega($conexion, $id_entrega, $calificacion, $comentarios_docente)) {
                $_SESSION['success_message'] = "Entrega calificada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al calificar la entrega.";
            }
            // Redirigir a la página de entregas de la tarea
            header("Location: ../vistas/docente/ver_entregas_tarea.php?id_tarea=" . $id_tarea);
            exit();

        // Acciones de Estudiante
        case 'entregar_tarea':
            if ($_SESSION['rol'] !== 'estudiante' || $id_estudiante == 0) { $_SESSION['error_message'] = "Acceso denegado."; break; }
            $tipo_entrega = $_POST['tipo_entrega'] ?? 'archivo';
            $file_data = ($tipo_entrega == 'archivo') ? ($_FILES['archivo_entrega'] ?? null) : null;
            $texto_entrega = ($tipo_entrega == 'texto') ? ($_POST['texto_entrega'] ?? null) : null;

            if (TareaController::entregarTarea($conexion, $id_tarea, $id_estudiante, $tipo_entrega, $file_data, $texto_entrega)) {
                $_SESSION['success_message'] = "Entrega realizada exitosamente.";
            } else {
                if (!isset($_SESSION['error_message'])) { // Si el error no fue seteado por la lógica de subida de archivo
                    $_SESSION['error_message'] = "Error al realizar la entrega.";
                }
            }
            header("Location: ../vistas/estudiante/detalle_tarea.php?id_tarea=" . $id_tarea);
            exit();

        default:
            $_SESSION['error_message'] = "Acción no válida.";
            // Redirigir a una página de error o al dashboard
            header("Location: ../../index.php");
            exit();
    }
    // Si no hubo redirección en el switch, redirigir al dashboard
    header("Location: ../../index.php");
    exit();
}
