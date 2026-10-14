<?php
$pageTitle = $pageTitle ?? 'CapacitaLíder';
$sectionTitle = $sectionTitle ?? 'Dashboard';
$activePage = $activePage ?? '';
$basePath = $basePath ?? '';
$pageStyle = $pageStyle ?? '';
$pageVendorStyles = $pageVendorStyles ?? [];
$basePathEscaped = htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plataforma de capacitación y evaluación de personal">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="stylesheet" href="<?= $basePathEscaped ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $basePathEscaped ?>css/all.min.css">
    <link rel="stylesheet" href="<?= $basePathEscaped ?>css/global.css">
    <link rel="stylesheet" href="<?= $basePathEscaped ?>css/layout.css">
<?php foreach ($pageVendorStyles as $vendorStyle): ?>
    <link rel="stylesheet" href="<?= $basePathEscaped . htmlspecialchars($vendorStyle, ENT_QUOTES, 'UTF-8') ?>">
<?php endforeach; ?>
<?php if ($pageStyle !== ''): ?>
    <link rel="stylesheet" href="<?= $basePathEscaped . htmlspecialchars($pageStyle, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
</head>
<body class="app-shell sidebar-collapsed">
<?php include __DIR__ . '/sidebar.php'; ?>

    <div id="sidebar-overlay" class="sidebar-overlay" aria-hidden="true"></div>

<?php include __DIR__ . '/topbar.php'; ?>

    <main class="app-main">
