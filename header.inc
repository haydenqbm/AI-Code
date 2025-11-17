<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensure content can be displayed properly across different devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Nexora IT Solutions | Empowering Tomorrow\'s Technology'; ?></title>
    <!-- Link to the universal stylesheet for this project -->
    <link rel="stylesheet" href="styles/styles.css">
    <?= isset($additionalMeta) ? $additionalMeta : ''; ?>
</head>
<body>
<?php include 'nav.inc'; ?>