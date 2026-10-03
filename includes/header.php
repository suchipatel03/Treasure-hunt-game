<?php
require_once __DIR__ . '/../includes/session.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$playerName  = getPlayerName();
$playerKnown = isUserRegistered();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Treasure Hunt Adventure') ?></title>
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>css/style.css">
</head>
<body>
<div class="bg-wrapper">
<nav class="navbar">
    <a class="navbar-brand" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php">🗺️ Treasure Hunt</a>
    <?php if ($playerKnown): ?>
    <div class="navbar-player">Playing as <span><?= htmlspecialchars($playerName) ?></span></div>
    <button class="btn-logout" id="logout-btn">Leave Hunt</button>
    <?php endif; ?>
</nav>
<main class="main-content">
