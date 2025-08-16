<?php
require_once __DIR__ . '/../../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - <?php echo htmlspecialchars(SITE_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo ASSETS_URL; ?>/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/mangas.php">Mangas</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/genres.php">Genres</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/authors.php">Authors</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/messages.php">Messages</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/retriever.php">Retriever/Upload</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>" target="_blank">View Site</a></li>
        <?php if (is_logged_in()): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">