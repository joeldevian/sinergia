<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar_pago_general':
        agregarPagoGeneral();
        break;
    default:
        $_SESSION['mensaje'] = "Acción no válida.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_pagos.php");
        exit();
}

function agregarPagoGeneral() {
    try {
        // 1. Recoger y validar datos
        $id_estudiante = filter_input(INPUT_POST, 'id_estudiante', FILTER_VALIDATE_INT);
        $concepto = trim($_POST['concepto'] ?? '');
        $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
        $fecha_pago = $_POST['fecha_pago'] ?? '';
        $metodo_pago = trim($_POST['metodo_pago'] ?? '');

        if (!$id_estudiante || empty($concepto) || $monto === false || $monto <= 0 || empty($fecha_pago) || empty($metodo_pago)) {
            throw new Exception("Todos los campos son requeridos y el monto debe ser un número positivo.");
        }

        // 2. Generar número de recibo único
        $last_receipt = select_one("SELECT numero_recibo FROM pagos ORDER BY CAST(SUBSTRING(numero_recibo, 3) AS UNSIGNED) DESC LIMIT 1");
        $next_receipt_num = $last_receipt ? ((int)substr($last_receipt['numero_recibo'], 2)) + 1 : 1;
        $numero_recibo = 'R-' . str_pad($next_receipt_num, 6, '0', STR_PAD_LEFT);

        // 3. Insertar el nuevo pago en la base de datos (usando la nueva estructura)
        $sql = "INSERT INTO pagos (id_estudiante, numero_recibo, concepto, monto, fecha_pago, metodo_pago, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'válido')";
        
        $pago_id = execute_cud($sql, "issdss", [$id_estudiante, $numero_recibo, $concepto, $monto, $fecha_pago, $metodo_pago], true);

        if (!$pago_id) {
            throw new Exception("No se pudo registrar el pago en la base de datos.");
        }

        // 4. Guardar ID en sesión y redirigir a la página de confirmación
        $_SESSION['pago_summary'] = [
            'pago_id' => $pago_id
        ];

        header("Location: ../vistas/admin/confirmacion_pago.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar el pago: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
        // Redirigir de vuelta al formulario de pago en caso de error
        header("Location: ../vistas/admin/agregar_pago.php");
        exit();
    }
}
?>