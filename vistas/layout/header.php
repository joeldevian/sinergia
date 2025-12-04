<?php
// This file will be included by role-specific headers, which will handle session_start() and role checks.
// It expects $pageTitle to be set by the including file.
// It also expects $_SESSION['username'] to be available for the top nav.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - INSTITUTO SINERGIA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/estilos.css?v=1.2">
    <link rel="stylesheet" href="../../assets/css/toastify.min.css">
    <?php if (isset($includeDataTablesCss) && $includeDataTablesCss): ?>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">INSTITUTO SINERGIA</div>
        <div class="list-group list-group-flush">
            <?php
            // Sidebar links will be included here dynamically based on role
            // This part will be handled by vistas/layout/sidebar.php
            ?>