<?php
require_once __DIR__ . '/init.php';
?><!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(SITE_NAME); ?><?php echo isset($page_title) ? ' - ' . e($page_title) : ''; ?></title>
    <meta name="description" content="Read manga online at <?php echo e(SITE_NAME); ?> - a modern, anime-tech themed manga reader.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo e(base_url('assets/css/style.css')); ?>" rel="stylesheet">
    <link rel="icon" href="<?php echo e(base_url('assets/images/logo.svg')); ?>" type="image/svg+xml">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body class="cm-body">
<nav class="navbar navbar-expand-lg navbar-dark cm-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?php echo e(base_url('/')); ?>">
      <img src="<?php echo e(base_url('assets/images/logo.svg')); ?>" alt="logo" width="28" height="28" class="me-2">
      <span class="fw-bold">Crypyed<span class="text-gradient">Manga</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('mangas')); ?>">Manga List</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('about')); ?>">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('contact')); ?>">Contact</a></li>
      </ul>
      <form class="d-flex me-2" role="search" method="get" action="<?php echo e(base_url('mangas')); ?>">
        <input class="form-control form-control-sm me-2" type="search" placeholder="Search mangas" name="q" aria-label="Search">
        <button class="btn btn-sm btn-outline-info" type="submit"><i class="bi bi-search"></i></button>
      </form>
      <ul class="navbar-nav mb-2 mb-lg-0">
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('bookmarks')); ?>"><i class="bi bi-bookmark-heart"></i> Bookmarks</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('history')); ?>"><i class="bi bi-clock-history"></i> History</a></li>
          <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('admin/')); ?>"><i class="bi bi-speedometer2"></i> Admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('logout')); ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('login')); ?>"><i class="bi bi-person"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('register')); ?>"><i class="bi bi-person-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="py-4">
  <div class="container">
    <?php echo flash_render(); ?>