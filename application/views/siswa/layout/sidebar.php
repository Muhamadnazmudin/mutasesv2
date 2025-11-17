<?php $active = isset($active) ? $active : ''; ?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo site_url('SiswaDashboard'); ?>">
        <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user-graduate"></i></div>
        <div class="sidebar-brand-text mx-3">SISWA</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?php echo ($active=='dashboard'?'active':''); ?>">
        <a class="nav-link" href="<?php echo site_url('SiswaDashboard'); ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- <div class="sidebar-heading">Menu Siswa</div> -->

    <li class="nav-item <?php echo ($active=='biodata'?'active':''); ?>">
        <a class="nav-link" href="<?php echo site_url('SiswaDashboard/biodata'); ?>">
            <i class="fas fa-id-card"></i> <span>Biodata Lengkap</span>
        </a>
    </li>

    <li class="nav-item <?php echo ($active=='cetak'?'active':''); ?>">
        <a class="nav-link" href="<?php echo site_url('SiswaDashboard/cetak'); ?>">
            <i class="fas fa-file-pdf"></i> <span>Cetak Biodata PDF</span>
        </a>
    </li>
    <li class="nav-item <?php echo ($active=='kartu'?'active':''); ?>">
    <a class="nav-link" href="<?php echo site_url('SiswaDashboard/kartu'); ?>">
        <i class="fas fa-id-badge"></i> <span>Kartu Siswa</span>
    </a>
</li>
<li class="nav-item <?php echo ($active=='idcard'?'active':''); ?>">
    <a class="nav-link" href="<?php echo site_url('SiswaDashboard/idcard'); ?>">
        <i class="fas fa-id-card"></i> <span>ID Card Siswa</span>
    </a>
</li>


    <!-- <li class="nav-item <?php echo ($active=='mutasi'?'active':''); ?>">
        <a class="nav-link" href="<?php echo site_url('SiswaDashboard/mutasi'); ?>">
            <i class="fas fa-random"></i> <span>Riwayat Mutasi</span>
        </a>
    </li>

    <li class="nav-item <?php echo ($active=='password'?'active':''); ?>">
        <a class="nav-link" href="<?php echo site_url('SiswaDashboard/password'); ?>">
            <i class="fas fa-lock"></i> <span>Ubah Password</span>
        </a> -->
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center mb-2">
        <a href="<?php echo site_url('SiswaAuth/logout'); ?>" class="btn btn-danger btn-sm w-75 shadow-sm">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="text-center d-none d-md-inline mb-3">
        <button class="rounded-circle border-0 bg-white shadow-sm" id="sidebarToggle">
            <i class="fas fa-angle-double-left text-primary"></i>
        </button>
    </div>

</ul>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown no-arrow">
                    <span class="nav-link text-gray-800">
                        <?php echo $this->session->userdata('siswa_nama'); ?> | <strong>Siswa</strong>
                    </span>
                </li>
            </ul>
        </nav>

        <div class="container-fluid">
