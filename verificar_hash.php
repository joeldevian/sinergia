<?php
echo "<h1>Verificación de Contraseña</h1>";

$password_to_test = 'password123';
$existing_hash_from_sql = '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.';

echo "<p><strong>Contraseña a probar:</strong> " . htmlspecialchars($password_to_test) . "</p>";
echo "<p><strong>Hash existente en el script SQL:</strong> " . htmlspecialchars($existing_hash_from_sql) . "</p>";
echo "<hr>";

// 1. Verificar si el hash existente es válido para la contraseña
$is_valid = password_verify($password_to_test, $existing_hash_from_sql);

echo "<h2>Resultado de la Verificación</h2>";
if ($is_valid) {
    echo "<p style='color:green; font-weight:bold;'>ÉXITO: La contraseña 'password123' COINCIDE con el hash existente.</p>";
    echo "<p>El problema podría estar en otro lugar. Revisa que el nombre de usuario ('j.perez') y el estado ('activo') sean correctos en la base de datos.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>FALLO: La contraseña 'password123' NO COINCIDE con el hash existente.</p>";
    echo "<p>Esto confirma que el hash es el problema. Probablemente se deba a una diferencia en la versión de PHP o sus librerías.</p>";
    
    // 2. Generar un nuevo hash correcto en este sistema
    echo "<hr>";
    echo "<h2>Nuevo Hash Generado</h2>";
    $new_hash = password_hash($password_to_test, PASSWORD_DEFAULT);
    echo "<p>Usa este nuevo hash para actualizar las contraseñas en la base de datos:</p>";
    echo "<pre style='background-color:#eee; padding:10px; border:1px solid #ccc;'>" . htmlspecialchars($new_hash) . "</pre>";
}
?>
