<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Izin Pulang</title>

<style>
@page {
    size: 80mm auto;
    margin: 0;
}
body {
    width: 80mm;
    margin: 0;
    padding: 6px;
    font-family: Arial, sans-serif;
    font-size: 11px;
}
.border-box {
    border: 2px solid #000;
}

/* HEADER */
.header-box {
    background:#0000;
    border-bottom:2px solid #000;
    border-top:2px solid #000;
    padding:5px 2px;
    display:flex;
    align-items:center;
}
.header-logo {
    width:45px;
    text-align:center;
}
.header-logo img {
    width:45px;
}
.header-text {
    flex:1;
    text-align:center;
    font-size:10px;
    line-height:1.15;
}
.header-text .school {
    font-size:12px;
    font-weight:bold;
    margin-top:2px;
}

.surat-title {
    text-align:center;
    font-weight:bold;
    margin:6px 0;
    padding:3px 0;
    border-top:1px solid #000;
    border-bottom:1px solid #000;
}

/* TABLE */
table { width: 100%; }
td.label { width: 28mm; vertical-align: top; }

/* TTD */
.ttd-wrapper {
    width: 100%;
    margin-top: 20px;
}
.ttd-left, .ttd-right {
    width: 49%;
    display: inline-block;
    font-size: 10.5px;
    vertical-align: top;
}
.ttd-left { text-align: left; }
.ttd-right { text-align: right; }

.walikelas {
    text-align:center;
    margin-top:35px;
}
</style>
</head>

<body onload="window.print()">

<div class="border-box">

    <!-- HEADER BARU -->
    <div class="header-box">
        <div class="header-logo">
            <img src="<?= base_url('assets/img/logobonti.png') ?>">
        </div>
        <div class="header-text">
            <div><b>PEMERINTAH DAERAH PROVINSI JAWA BARAT</b></div>
            <div><b>DINAS PENDIDIKAN</b></div>
            <div><b>CABANG DINAS PENDIDIKAN X</b></div>
            
            <div class="school">SMK NEGERI 1 CILIMUS</div>

            <div style="font-size:9px; margin-top:3px;">
                Jalan Baru Lingkar Caracas Cilimus<br>
                Telp. (0232) 8910145, Email: smkn_1cilimus@yahoo.com<br>
                Kabupaten Kuningan 45556
            </div>
        </div>
    </div>

    <div class="surat-title">SURAT IZIN PULANG</div>

    <div style="padding:5px;">
        <table>
            <tr><td class="label">Nama Siswa</td><td>: <?= $izin->nama ?></td></tr>
            <tr><td class="label">Kelas</td><td>: <?= $izin->kelas_nama ?></td></tr>
            <tr><td class="label">Jam Pulang</td><td>: <?= $izin->jam_keluar ?></td></tr>
            <tr><td class="label">Alasan</td><td>: <?= $izin->keperluan ?></td></tr>
            <tr><td class="label">Guru Mapel</td><td>: <?= $guru_mapel->nama ?></td></tr>
        </table>
    </div>

    <!-- TTD -->
    <div class="ttd-wrapper">

        <!-- KIRI: GURU MAPEL -->
        <div class="ttd-left"><br>
            Guru Mata Pelajaran,<br><br><br><br>
            <b><?= $guru_mapel->nama ?></b><br>
            NIP. <?= $guru_mapel->nip ?>
        </div>

        <!-- KANAN: PETUGAS PIKET -->
        <div class="ttd-right">
            Kuningan, <?= date('d-m-Y') ?><br>
            Petugas Piket,<br><br><br><br>
            <b><?= $piket->nama ?></b><br>
            NIP. <?= $piket->nip ?>
        </div>

    </div>

    <!-- WALI KELAS -->
    <div class="walikelas">
        Wali Kelas,<br><br><br>
        <b><?= $walikelas->nama ?></b><br>
        NIP. <?= $walikelas->nip ?>
    </div>

</div>

</body>
</html>
