<?php
// This file should be included by controllers after 'conexion.php'
// Or it can include it itself. Let's include it here for simplicity.
if (file_exists(__DIR__ . '/conexion.php')) {
    require_once __DIR__ . '/conexion.php';
}

/**
 * A general purpose query function that uses prepared statements.
 *
 * @param string $sql The SQL query with '?' placeholders.
 * @param string $types A string containing the types for each parameter (e.g., "sis").
 * @param array $params The array of parameters to bind.
 * @return mysqli_stmt|bool The statement object on success, or false on failure.
 */
function query($sql, $types = "", $params = []) {
    global $conexion;

    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        // In a real app, you'd log this error.
        // error_log("Prepare failed: (" . $conexion->errno . ") " . $conexion->error);
        return false;
    }

    if ($types != "" && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        // error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        return false;
    }

    return $stmt;
}

/**
 * Fetches all rows from a SELECT query.
 *
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return array
 */
function select_all($sql, $types = "", $params = []) {
    $stmt = query($sql, $types, $params);
    if ($stmt === false) return [];
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

/**
 * Fetches a single row from a SELECT query.
 *
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return array|null
 */
function select_one($sql, $types = "", $params = []) {
    $stmt = query($sql, $types, $params);
    if ($stmt === false) return null;
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Executes an INSERT, UPDATE, or DELETE query.
 *
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return bool
 */
function execute_cud($sql, $types = "", $params = []) {
    $stmt = query($sql, $types, $params);
    if ($stmt === false) return false;
    $stmt->close();
    return true;
}

/**
 * Gets the ID of the last inserted row.
 *
 * @return int
 */
function last_insert_id() {
    global $conexion;
    return $conexion->insert_id;
}

?>
