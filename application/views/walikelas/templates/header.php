<!-- ================= HEADER WALIKELAS ================= -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? $title.' - Mutases' : 'Mutases - Wali Kelas' ?></title>

  <!-- FontAwesome -->
  <link href="<?= base_url('assets/sbadmin2/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">

  <!-- Bootstrap -->
  <link href="<?= base_url('assets/sbadmin2/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">

  <!-- SB Admin 2 -->
  <link href="<?= base_url('assets/sbadmin2/css/sb-admin-2.min.css') ?>" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="<?= base_url('assets/css/custom-style.css') ?>" rel="stylesheet">

  <style>
/* ===============================
   LIGHT MODE
   =============================== */
body.light-mode {
    background-color: #f0f4ff !important;
    color: #1a1a1a !important;
}

body.light-mode .card {
    background: white !important;
    border: 1px solid #dce4ff !important;
}

body.light-mode .topbar {
    background: #f9fbff !important;
}

/* ===============================
   FIX SIDEBAR & TOPBAR (NORMAL SB ADMIN)
   =============================== */

.sidebar {
    height: 100vh;
}

.topbar {
    z-index: 1040 !important;
}

#content-wrapper {
    min-height: 100vh;
    padding-top: 90px
}

/* HILANGKAN semua override yang merusak layout */
html, body {
    overflow: auto !important;
    height: auto !important;
}

  </style>
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">
