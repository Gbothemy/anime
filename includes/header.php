<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/seo.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$metaDescription = $metaDescription ?? 'Read manga online with a responsive, mobile-friendly reader.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/')); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><?php echo htmlspecialchars(SITE_NAME); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>manga_list.php">Manga List</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
        <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin</a></li>
        <?php endif; ?>
      </ul>
      <form class="d-flex" role="search" action="<?php echo BASE_URL; ?>manga_list.php" method="get">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        <button class="btn btn-outline-light" type="submit">Search</button>
      </form>
      <ul class="navbar-nav ms-3">
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>bookmarks.php"><i class="bi bi-bookmark"></i> Bookmarks</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>history.php"><i class="bi bi-clock-history"></i> History</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(current_user()['name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">