<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Izin Keluar</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
    body {
        font-family: "Segoe UI", sans-serif;
        padding: 20px;
        background: #f8f9fa;
    }

    .surat-box {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        width: 750px;
        margin: auto;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: 1px solid #ddd;
    }

    @media print {
        .btn-print { display: none; }
        .surat-box { box-shadow: none; border: none; }
    }
</style>
</head>
<body>

<div class="surat-box">

    <div class="text-center mb-3">
        <img src="<?= base_url('assets/logo.png') ?>" alt="Logo" style="width:70px;">
        <h2 class="fw-bold mt-2">SURAT IZIN <?= strtoupper($izin->jenis_izin) ?></h2>
        <div class="text-muted">SMK MUTASES DIGITAL SCHOOL</div>
        <hr>
    </div>

    <table class="info-table" width="100%">
        <tr><td width="150">Nama</td><td>: <b><?= $izin->nama ?></b></td></tr>
        <tr><td>Kelas</td><td>: <?= $izin->kelas_nama ?></td></tr>
        <tr><td>Jenis Izin</td><td>: <b><?= ucfirst($izin->jenis_izin) ?></b></td></tr>
        <tr><td>Jam Keluar</td><td>: <?= $izin->jam_keluar ?></td></tr>

        <tr>
            <td>Estimasi</td>
            <td>: 
                <?= ($izin->jenis_izin == 'keluar') ? $izin->estimasi_menit . ' menit' : '-' ?>
            </td>
        </tr>

        <tr><td>Keperluan</td><td>: <?= $izin->keperluan ?></td></tr>
    </table>

    <?php if ($izin->jenis_izin == 'keluar'): ?>

        <?php 
            $qr_url = base_url('index.php/izin/kembali/' . $izin->token_kembali);
            $qr_image = 'https://quickchart.io/qr?text=' . urlencode($qr_url) . '&size=170';
        ?>
        <div class="text-center mt-4">
            <img src="<?= $qr_image ?>" alt="QR Code">
            <p class="text-muted small">Scan untuk menandai bahwa siswa sudah kembali</p>
        </div>

    <?php else: ?>

        <div class="alert alert-danger text-center mt-4">
            <b>Izin Pulang</b> — Siswa tidak kembali ke sekolah.
        </div>

    <?php endif; ?>

    <div class="text-end mt-3 fst-italic text-muted">
        Dicetak otomatis oleh Sistem Mutases
    </div>
</div>

<div class="text-center mt-3 d-flex justify-content-center gap-2 btn-print">

    <a href="<?= base_url('') ?>" 
       class="btn btn-secondary">
        Kembali
    </a>

    <button onclick="window.print()" class="btn btn-primary">
        Cetak Surat
    </button>

</div>


</body>
</html>
