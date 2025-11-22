<?php
// Ambil tahun ajaran aktif
$tahun_id = $this->session->userdata('tahun_id');
$tahun = $this->db->get_where('tahun_ajaran', ['id' => $tahun_id])->row();

// Nama Wali & kelas
$nama_wali = $this->session->userdata('nama');
$kelas_label = $kelas_nama;
?>

<div class="container-fluid">

    <!-- ============================= -->
    <!-- HEADER SELAMAT DATANG -->
    <!-- ============================= -->
    <?php if ($this->session->userdata('logged_in')): ?>
<div class="text-center mt-4 mb-5">
  <h3>Selamat Datang, <?= $this->session->userdata('nama'); ?> 👋</h3>

  <p class="text-muted">
    Anda login sebagai <strong>Wali Kelas</strong><br>
    Kelas yang Anda ampu: <strong><?= $kelas_nama ?></strong><br>
    Tahun Ajaran Aktif:
    <strong>
      <?php 
        $tahun_id = $this->session->userdata('tahun_id');
        $tahun = $this->db->get_where('tahun_ajaran', ['id' => $tahun_id])->row();
        echo $tahun ? $tahun->tahun : '-';
      ?>
    </strong>
  </p>
</div>
<?php endif; ?>


    <h3 class="fw-bold mb-4">
        Dashboard Wali Kelas — <?= $kelas_nama ?>
    </h3>

    <div class="row">

        <!-- ============================= -->
        <!-- CARD 1 — SISWA KELAS -->
        <!-- ============================= -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="text-primary fw-bold mb-3">
                        <i class="bi bi-people-fill"></i> Siswa di Kelas Ini
                    </h5>

                    <table class="table table-sm">
                        <tr>
                            <td>Laki-laki</td>
                            <td class="fw-bold"><?= $laki ?></td>
                        </tr>
                        <tr>
                            <td>Perempuan</td>
                            <td class="fw-bold"><?= $perempuan ?></td>
                        </tr>

                        <tr class="bg-dark bg-opacity-10 border-top"><br><br><br>
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold"><?= $total_siswa ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================= -->
        <!-- CARD 2 — KEHADIRAN HARI INI -->
        <!-- ============================= -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="text-success fw-bold mb-3">
                        <i class="bi bi-calendar-check"></i> Kehadiran Hari Ini
                    </h5>

                    <table class="table table-sm">
                        <tr><td>Hadir</td><td class="fw-bold"><?= $hari_H ?></td></tr>
                        <tr><td>Izin</td><td class="fw-bold"><?= $hari_I ?></td></tr>
                        <tr><td>Sakit</td><td class="fw-bold"><?= $hari_S ?></td></tr>
                        <tr><td>Alpa</td><td class="fw-bold"><?= $hari_A ?></td></tr>

                        <tr class="bg-dark bg-opacity-10 border-top">
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold"><?= $hari_H + $hari_I + $hari_S + $hari_A ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================= -->
        <!-- CARD 3 — KEHADIRAN BULAN INI -->
        <!-- ============================= -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="text-warning fw-bold mb-3">
                        <i class="bi bi-calendar-month"></i> Kehadiran Bulan Ini
                    </h5>

                    <table class="table table-sm">
                        <tr><td>Hadir</td><td class="fw-bold"><?= $bulan_H ?></td></tr>
                        <tr><td>Izin</td><td class="fw-bold"><?= $bulan_I ?></td></tr>
                        <tr><td>Sakit</td><td class="fw-bold"><?= $bulan_S ?></td></tr>
                        <tr><td>Alpa</td><td class="fw-bold"><?= $bulan_A ?></td></tr>

                        <tr class="bg-dark bg-opacity-10 border-top">
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold"><?= $bulan_H + $bulan_I + $bulan_S + $bulan_A ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================= -->
    <!-- ROW 2 — IZIN -->
    <!-- ============================= -->
    <div class="row mt-3">

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="text-danger fw-bold mb-3">
                        <i class="bi bi-door-open-fill"></i> Izin Siswa Hari Ini
                    </h5>

                    <table class="table table-sm">
                        <tr><td>Izin Keluar</td><td class="fw-bold"><?= $izin_keluar_hari_ini ?></td></tr>
                        <tr><td>Izin Pulang</td><td class="fw-bold"><?= $izin_pulang_hari_ini ?></td></tr>

                        <tr class="bg-dark bg-opacity-10 border-top">
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold"><?= $izin_keluar_hari_ini + $izin_pulang_hari_ini ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- GRAFIK / FUTURE -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center text-muted d-flex justify-content-center align-items-center">
                    <div>
                        <i class="bi bi-graph-up-arrow fs-1"></i>
                        <p class="mt-2">
                            Grafik Kehadiran<br>
                            (akan dibuat jika diperlukan)
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
