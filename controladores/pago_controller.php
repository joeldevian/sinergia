<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$id_estudiante_redirect = $_POST['id_estudiante'] ?? $_GET['id_estudiante'] ?? 0;

switch ($accion) {
    case 'agregar_pension':
        agregarPension($id_estudiante_redirect);
        break;
    case 'agregar_pago':
        agregarPago($id_estudiante_redirect);
        break;
    default:
        $_SESSION['mensaje'] = "Acción no válida.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_pagos.php");
        exit();
}

function agregarPension($id_estudiante) {
    try {
        $periodo = trim($_POST['periodo_academico'] ?? '');
        $monto = filter_var($_POST['monto'] ?? '', FILTER_VALIDATE_FLOAT);
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

        if (empty($periodo) || $monto === false || $monto <= 0 || empty($fecha_vencimiento)) {
            throw new Exception("Todos los campos son requeridos y el monto debe ser un número positivo.");
        }

        $sql = "INSERT INTO pensiones (id_estudiante, periodo_academico, monto, fecha_vencimiento, estado) VALUES (?, ?, ?, ?, 'pendiente')";
        
        if (!execute_cud($sql, "isds", [$id_estudiante, $periodo, $monto, $fecha_vencimiento])) {
            throw new Exception("No se pudo asignar la nueva pensión.");
        }

        $_SESSION['mensaje'] = "Pensión asignada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al asignar la pensión: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/detalle_pagos_estudiante.php?id_estudiante=" . $id_estudiante);
    exit();
}

function agregarPago($id_estudiante) {
    try {
        $id_pension = filter_var($_POST['id_pension'] ?? '', FILTER_VALIDATE_INT);
        $monto_pagado = filter_var($_POST['monto_pagado'] ?? '', FILTER_VALIDATE_FLOAT);
        $fecha_pago = $_POST['fecha_pago'] ?? '';
        $metodo_pago = trim($_POST['metodo_pago'] ?? '');

        if ($id_pension === false || $monto_pagado === false || $monto_pagado <= 0 || empty($fecha_pago) || empty($metodo_pago)) {
            throw new Exception("Todos los campos son requeridos y el monto debe ser un número positivo.");
        }

        // Opcional: Validar que el monto pagado no exceda el saldo pendiente de la pensión
        $pension_info = select_one("SELECT monto FROM pensiones WHERE id = ?", "i", [$id_pension]);
        $pagos_previos = select_one("SELECT SUM(monto_pagado) as total_pagado FROM pagos WHERE id_pension = ?", "i", [$id_pension]);
        $saldo_pendiente = $pension_info['monto'] - ($pagos_previos['total_pagado'] ?? 0);

        if ($monto_pagado > $saldo_pendiente) {
            throw new Exception("El monto a pagar (S/ " . number_format($monto_pagado, 2) . ") no puede ser mayor que el saldo pendiente (S/ " . number_format($saldo_pendiente, 2) . ").");
        }

        $sql = "INSERT INTO pagos (id_pension, monto_pagado, fecha_pago, metodo_pago) VALUES (?, ?, ?, ?)";
        
        if (!execute_cud($sql, "idss", [$id_pension, $monto_pagado, $fecha_pago, $metodo_pago])) {
            throw new Exception("No se pudo registrar el pago.");
        }

        $_SESSION['mensaje'] = "Pago registrado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar el pago: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/detalle_pagos_estudiante.php?id_estudiante=" . $id_estudiante);
    exit();
}
?>
